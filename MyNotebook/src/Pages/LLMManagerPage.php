<?php
namespace MyNotebook\Pages;

class LLMManagerPage {
    public function render( $output ) {
        // Giao diện thuần HTML, không chứa logic JS
        $html = "
            <div style='display: flex; gap: 20px; font-family: sans-serif; margin-top: 15px;'>
                
                <div style='flex: 1; border: 1px solid #ddd; padding: 15px; border-radius: 8px; background: #fff;'>
                    <button id='btn_go_back' style='padding: 6px 12px; background: #e2e8f0; color: #334155; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; display: flex; align-items: center; gap: 5px; margin-bottom: 15px;'>
                        &larr; Quay lại
                    </button>

                    <h3 style='margin-top: 0;'>Danh sách Mô hình AI</h3>

                    <div id='llm-list-container'>
                        <p style='color: #666;'>Đang tải dữ liệu...</p>
                    </div>
                </div>

                <div style='flex: 1; border: 1px solid #ddd; padding: 15px; border-radius: 8px; background: #f8fafc;'>
                    <h3 style='margin-top: 0;' id='form-title'>Thêm cấu hình LLM mới</h3>
                    <form id='llmForm'>
                        <input type='hidden' id='form_id' value=''>
                        
                        <div style='margin-bottom: 10px;'>
                            <label style='font-weight: bold;'>Nhà cung cấp (Provider):</label><br>
                            <input type='text' id='form_provider' placeholder='VD: OpenAI, Groq, Gemini...' style='width: 100%; padding: 8px; box-sizing: border-box;'>
                        </div>
                        
                        <div style='margin-bottom: 10px;'>
                            <label style='font-weight: bold;'>Base URL:</label><br>
                            <input type='text' id='form_base_url' placeholder='VD: https://api.openai.com/v1' style='width: 100%; padding: 8px; box-sizing: border-box;'>
                        </div>
                        
                        <div style='margin-bottom: 10px;'>
                            <label style='font-weight: bold;'>API Key:</label><br>
                            <input type='password' id='form_api_key' placeholder='Nhập API Key của bạn' style='width: 100%; padding: 8px; box-sizing: border-box;'>
                        </div>
                        
                        <div style='margin-bottom: 15px;'>
                            <label style='font-weight: bold;'>Tên mô hình (Model Name):</label><br>
                            <input type='text' id='form_model_name' placeholder='VD: gpt-4o, llama-3.1-8b-instant' style='width: 100%; padding: 8px; box-sizing: border-box;'>
                        </div>
                        
                        <div style='display: flex; gap: 10px;'>
                            <button type='button' id='btn_save' style='padding: 8px 15px; background: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer; flex: 1;'>
                                Thêm mới
                            </button>
                            <button type='button' id='btn_delete' disabled style='padding: 8px 15px; background: #dc2626; color: white; border: none; border-radius: 4px; cursor: not-allowed; opacity: 0.5;'>
                                Xóa
                            </button>
                            <button type='button' id='btn_cancel' disabled style='padding: 8px 15px; background: #64748b; color: white; border: none; border-radius: 4px; cursor: not-allowed; opacity: 0.5;'>
                                Thoát sửa
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        ";

        $output->addHTML( $html );
    }
}