import time
import re
from typing import List, Dict, Optional, Tuple, Any
from sentence_transformers import SentenceTransformer, CrossEncoder
from ai_core.llm_engine import LLMManager
from config_loader import load_chroma_client


class ChatComposer:
    def __init__(self,
                 session_id: str,
                 llm: LLMManager,
                 llm_small: Optional[LLMManager] = None,
                 embedding_model_name: str = "sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2"):

        self.session_id = session_id
        self.llm = llm
        self.llm_small = llm_small if llm_small is not None else llm

        self.chroma_client, self.collection = load_chroma_client()
        self.embedding_model = SentenceTransformer(embedding_model_name)

        self.reranker = CrossEncoder('itdainb/PhoRanker', max_length=256)
        self.reranker.tokenizer.model_max_length = 256
        self.reranker.max_seq_length = 256

    def _is_error_response(self, response: str) -> bool:
        error_keywords = ["lỗi", "error", "fail", "exception", "không kết nối", "connection failed"]
        return any(keyword.lower() in response.lower() for keyword in error_keywords)

    def _format_history(self, chat_history: Optional[List[Dict]]) -> str:
        if not chat_history:
            return "Không có."
        lines = []
        for turn in chat_history[-5:]:
            role = "Người dùng" if turn.get("role") == "user" else "Trợ lý"
            lines.append(f"{role}: {turn.get('content', '')}")
        return "\n".join(lines)

    def _get_relevant_chunks(self, query: str) -> List[Dict]:
        query_vector = self.embedding_model.encode(query).tolist()

        initial_k = 10
        results = self.collection.query(
            query_embeddings=[query_vector],
            n_results=initial_k,
            where={"doc_name": self.session_id}
        )

        if not results["documents"] or not results["documents"][0]:
            return []

        doc_list = results["documents"][0]
        meta_list = results["metadatas"][0]

        rerank_pairs = [[query, doc_text] for doc_text in doc_list]
        scores = self.reranker.predict(rerank_pairs)

        ranked_results = []
        for score, doc, meta in zip(scores, doc_list, meta_list):
            ranked_results.append({
                "score": score,
                "content": doc,
                "metadata": meta
            })

        ranked_results.sort(key=lambda x: x["score"], reverse=True)

        final_top_k = 5
        top_results = ranked_results[:final_top_k]

        return [{"content": item["content"], "metadata": item["metadata"]} for item in top_results]

    def _query_expansion(self, question: str, chat_history: Optional[List[Dict]] = None) -> str:
        prompt = f"""
        ### VAI TRÒ:
        Bạn là một chuyên gia tìm kiếm thông tin và tối ưu hóa truy vấn (SEO).
        ### NHIỆM VỤ:
        Hãy viết lại câu hỏi của người dùng thành một "câu truy vấn tìm kiếm" (search query) để tìm thông tin trong cơ sở dữ liệu.

        ### YÊU CẦU:
        1. Dựa vào lịch sử hội thoại (nếu có) để hiểu rõ ngữ cảnh và làm rõ các đại từ, từ viết tắt trong câu hỏi.
        2. Bổ sung các từ khóa, khái niệm chuyên ngành liên quan có thể xuất hiện trong tài liệu nguồn.
        3. Viết dưới dạng một đoạn văn ngắn hoặc các câu nối tiếp nhau, khoảng 30-50 từ.
        4. CHỈ TRẢ VỀ NỘI DUNG CÂU TRUY VẤN, KHÔNG GIẢI THÍCH.
        5. KHÔNG GHI NGUYÊN LỊCH SỬ HỘI THOẠI VÀO CÂU TRUY VẤN, CHỈ TẬP TRUNG MỞ RỘNG CÂU HỎI HIỆN TẠI.

        ### LỊCH SỬ HỘI THOẠI GẦN NHẤT:
        {self._format_history(chat_history)}

        ### CÂU HỎI HIỆN TẠI:
        {question}
        """

        wait_count = 0
        max_waits = 3
        _small_fallbacks = ["groq/compound-mini", "meta-llama/llama-4-scout-17b-16e-instruct"]
        fallback_idx = 0

        while True:
            try:
                expanded_query = self.llm_small.send_prompt(prompt[:1500], options={"temperature": 0.5, "max_tokens": 1024})

                if self._is_error_response(expanded_query):
                    print(f"Query Expansion error (error in response): {expanded_query}")
                    if self.llm_small.provider == "OpenAI" and fallback_idx < len(_small_fallbacks):
                        self.llm_small.model_name = _small_fallbacks[fallback_idx]
                        fallback_idx += 1
                        print(f"Switching llm_small model to: {self.llm_small.model_name}")
                        continue
                    if wait_count < max_waits:
                        wait_count += 1
                        print(f"Waiting 60 seconds (attempt {wait_count}/{max_waits})...")
                        time.sleep(60)
                        continue
                    else:
                        print("Query Expansion error - max retries exceeded")
                        return question

                return expanded_query.strip().strip('"').strip("'")[:300]
            except Exception as e:
                print(f"Query Expansion error: {e}")
                if wait_count < max_waits:
                    wait_count += 1
                    time.sleep(10)
                    continue
                else:
                    print("Query Expansion error - max retries exceeded")
                    return question

    def answer_question(self, question: str, chat_history: Optional[List[Dict]] = None) -> Dict[str, Any]:
        search_query = self._query_expansion(question, chat_history)

        chunks = self._get_relevant_chunks(search_query)

        if not chunks:
            return {
                "answer": "Xin lỗi, tôi chưa tìm thấy thông tin liên quan trong tài liệu để trả lời câu hỏi này.",
                "sources": []
            }

        context_str = ""
        raw_sources_meta = []
        for item in chunks:
            context_str += f" {item['content']}"
            raw_sources_meta.append(item['metadata'])

        prompt = f"""
            ### VAI TRÒ:
            Bạn là một trợ lý AI trả lời câu hỏi dựa trên tài liệu được cung cấp.

            ### YÊU CẦU:
            1. Trả lời trực tiếp, ngắn gọn, đúng trọng tâm câu hỏi, dựa trên dữ liệu tham khảo bên dưới.
            2. Tuyệt đối KHÔNG sử dụng ký tự Markdown (*, #, **, __).
            3. Nếu dữ liệu tham khảo không đủ để trả lời, hãy nói rõ là chưa có đủ thông tin, không bịa đặt.
            4. Có thể tham khảo lịch sử hội thoại để hiểu ngữ cảnh câu hỏi.

            ### LỊCH SỬ HỘI THOẠI GẦN NHẤT:
            {self._format_history(chat_history)}

            ### CÂU HỎI:
            {question}

            ### DỮ LIỆU THAM KHẢO:
            {context_str}
            """

        wait_count = 0
        max_waits = 3
        _main_fallbacks = ["groq/compound", "openai/gpt-oss-120b", "qwen/qwen3-32b"]
        fallback_idx = 0

        while True:
            try:
                response = self.llm.send_prompt(prompt[:4000], options={"temperature": 0.1, "max_tokens": 2000})

                if self._is_error_response(response):
                    print(f"Answer question error (error in response): {response}")
                    if self.llm.provider == "OpenAI" and fallback_idx < len(_main_fallbacks):
                        self.llm.model_name = _main_fallbacks[fallback_idx]
                        fallback_idx += 1
                        print(f"Switching llm model to: {self.llm.model_name}")
                        continue
                    if wait_count < max_waits:
                        wait_count += 1
                        print(f"Waiting 60 seconds (attempt {wait_count}/{max_waits})...")
                        time.sleep(60)
                        continue
                    else:
                        print("Answer question error - max retries exceeded")
                        raise Exception("Answer question error - LLM failed (quá số lượng token cho phép)")

                clean_text = response.strip()
                clean_text = re.sub(r'[\*\#\_]', '', clean_text)
                clean_text = re.sub(r'\s+', ' ', clean_text)

                sources = self._build_sources(raw_sources_meta)

                return {"answer": clean_text, "sources": sources}
            except Exception as e:
                print(f"Answer question error: {e}")
                if wait_count < max_waits:
                    wait_count += 1
                    time.sleep(60)
                    continue
                else:
                    print("LLM error in answering question - max retries exceeded")
                    raise Exception("LLM error in answering question - max retries exceeded")

    def _build_sources(self, raw_sources_meta: List[Dict]) -> List[Dict]:
        sources = []
        seen = []
        for meta in raw_sources_meta:
            if meta in seen:
                continue
            seen.append(meta)
            sources.append({
                "id": len(sources) + 1,
                "locator": meta
            })
        return sources
