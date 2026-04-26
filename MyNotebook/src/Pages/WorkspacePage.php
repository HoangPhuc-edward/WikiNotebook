<?php
namespace MyNotebook\Pages;

class WorkspacePage {
    public function render( $output, $notebookId ) {
        if ( $notebookId === 0 ) {
            $output->addHTML( "<p>Lỗi ID</p>" );
            return;
        }

        $html = "
            <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
            <input type='hidden' id='ws_notebook_id' value='{$notebookId}'>
            
            <div class='ws-container'>
                
                <div class='ws-column ws-col-left' style='background-color: #ffffff; max-height: 100vh; overflow-y: auto; padding: 15px;'>
                    <button id='btn_go_back' class='ws-btn ws-btn-back'>
                        <i class='fas fa-angle-left'></i> Quay lại
                    </button>
                    
                    <button id='ws_btn_open_source_modal' class='ws-btn ws-btn-add-source'>
                        <span style='font-size: 18px;'>+</span> Thêm nguồn mới
                    </button>
                    
                    <h4 style='margin: 5px 0 10px 0; font-size: 15px; font-weight: 600; color: #333;'>Danh sách nguồn</h4>
                    
                    <div id='ws_source_status' style='color: #d97706; font-size: 13px; font-weight: 500; height: 18px; margin-bottom: 10px; font-style: italic;'></div>
                    <hr style='width: 100%; border: 0; border-top: 1px solid #e2e8f0; margin-bottom: 15px;'>
                    
                    <div id='ws_source_list' style='flex: 1; overflow-y: auto; font-size: 14px; line-height: 1.5;'>
                        <p style='color: #64748b; font-style: italic;'>Đang tải...</p>
                    </div>
                </div>
                <div class='ws-column ws-col-main' style='display: flex; flex-direction: column; gap: 20px;'>
    
                    <div style='display: flex; justify-content: space-between; align-items: center; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px;'>
                        <input type='hidden' id='ws_selected_template_id' value=''>
                        
                        <button type='button' id='ws_btn_open_template_modal' class='ws-btn' style='padding: 10px 15px; background: #2C7878; border: 2px solid #2C7878; color: white; font-size: 13px; font-weight: bold; border-radius: 8px; cursor: pointer;'>
                            Bấm chọn dàn ý
                        </button>
                        
                        <span id='ws_selected_template_name' style='font-size: 14px; color: #b91010; font-weight: 500;'>
                            Chưa chọn dàn ý
                        </span>
                    </div>

                    <div style='display: flex; flex-direction: column; background: #ffffff;  border-radius: 8px; padding: 15px; flex: 1;'>
                        
                        <div style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f1f5f9;'>
                            
                            <h3 id='ws_notebook_title' class='ws-title ws-title-lg' style='margin: 0; max-width: 60%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 18px; color: #1e293b;'>
                                Nội dung bài viết
                            </h3>
                            
                            <div style='display: flex; align-items: center; gap: 12px;'>
                                <span id='ws_status_text' style='color: #2563eb; font-size: 14px; font-weight: 500;'></span>
                                
                                <button id='ws_btn_generate' class='ws-btn' style='display: flex; align-items: center; gap: 8px; background: #2563eb; color: white; padding: 10px 20px; font-size: 14px; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;'>
                                    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512' style='width: 14px; height: 14px; fill: currentColor;'>
                                        <path d='M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z'/>
                                    </svg>
                                    Tạo bài viết
                                </button>
                            </div>
                        </div>

                        <div style='flex: 1; display: flex; flex-direction: column;'>
                            <div id='ws_article_view' class='ws-article-view' style='flex: 1;'>Không có</div>
                            <div id='ws_article_edit' style='display: none; flex-direction: column; gap: 15px;'></div>
                        </div>
                        
                    </div>
                </div>
                                
                <div class='ws-column ws-col-right'>
                    <h3 class='ws-title ws-title-sm'>Thao tác</h3>
                    <button id='ws_btn_edit' class='ws-btn ws-btn-action' style='background: #ffffff; border: 1px solid #cbd5e1; color: #334155;'>Sửa nội dung</button>
                    <button id='ws_btn_delete' class='ws-btn ws-btn-action' style='background: #fef2f2; color: #dc2626; border: 1px solid #fca5a5;'>Xoá notebook</button>
                    <button id='ws_btn_save' class='ws-btn ws-btn-action' style='background: #10b981; color: white;'>Lưu thay đổi</button>
                    <button id='ws_btn_mindmap' class='ws-btn ws-btn-action' style='background: #10a5b9; color: white;'>Sơ đồ tư duy</button>
                
                    <button id='ws_btn_upload' class='ws-btn ws-btn-action' style='margin-top: 12px; background: #1D9942; color: white;'>Gửi bài viết</button>
                </div>

                <div id='chunk_tooltip' class='ws-tooltip' style='display: none; pointer-events: none;'></div>
                
                <div id='ws_template_modal' class='ws-modal-overlay ws-modal-overlay-dark'>
                    <div class='ws-modal-box' style='width: 80%; max-width: 800px; max-height: 85vh; border-radius: 8px; overflow: hidden;'>
                        <div style='padding: 15px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;'>
                            <h3 class='ws-title ws-title-sm'>Chọn hoặc thêm nhanh dàn ý</h3>
                            <button id='ws_btn_close_modal' style='background: none; border: none; font-size: 1.8em; cursor: pointer; color: #64748b;'>&times;</button>
                        </div>
                        <div style='display: flex; flex: 1; overflow: hidden;'>
                            <div style='flex: 1.3; padding: 20px; border-right: 1px solid #e2e8f0; overflow-y: auto; background: #fafafa;'>
                                <input type='hidden' id='ws_editing_template_id' value=''>
                                <h4 style='margin-top: 0; color: #334155;'>Tạo nhanh dàn ý mới</h4>
                                <input type='text' id='ws_tpl_name' placeholder='Tên dàn ý...' style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box; margin-bottom: 15px;'>
                                <div style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;'>
                                    <span style='font-weight: 500; font-size: 14px; color: #334155;'>Cấu trúc bài viết:</span>
                                    <button type='button' id='ws_btn_add_section' class='ws-btn' style='padding: 5px 10px; background: #10b981; color: white; font-size: 12px;'>+ Thêm mục</button>
                                </div>
                                <div id='ws_tpl_structure_container' style='display: flex; flex-direction: column; gap: 10px;'></div>
                                <button type='button' id='ws_btn_save_select_tpl' class='ws-btn' style='margin-top: 20px; width: 100%; padding: 12px; background: #2563eb; color: white;'>Lưu & Chọn</button>
                            </div>
                            <div style='flex: 1; padding: 20px; overflow-y: auto; background: #ffffff; display: flex; flex-direction: column;'>
                                <h4 style='margin-top: 0; color: #334155;'>Danh sách đã lưu</h4>
                                
                                <div style='margin-bottom: 15px; position: relative;'>
                                    <input type='text' id='ws_tpl_search_input' placeholder='Tìm kiếm dàn ý...' 
                                        style='width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 13px; box-sizing: border-box;'
                                        oninput='filterWorkspaceTemplates()'>
                                </div>

                                <div id='ws_tpl_list_container' style='display: flex; flex-direction: column; gap: 10px; flex: 1; overflow-y: auto;'>
                                    <p style='color: #64748b; font-size: 14px; font-style: italic;'>Đang tải...</p>
                                </div>
                            </div>
                            </div>
                    </div>
                </div>

                <div id='ws_source_modal' class='ws-modal-overlay ws-modal-overlay-blur'>
                    <div class='ws-modal-box' style='width: 90%; max-width: 650px; border-radius: 16px; padding: 40px;'>
                        <button id='ws_btn_close_source_modal' style='position: absolute; top: 15px; right: 20px; background: none; border: none; font-size: 24px; cursor: pointer; color: #64748b; padding: 5px;'>&times;</button>
                        <div style='text-align: center; margin-bottom: 30px;'>
                            <h2 style='margin: 0; font-family: sans-serif !important; font-size: 1.6em; color: #1e293b; font-weight: 500;'>Thêm nguồn cho sổ tay</h2>
                        </div>
                        <div style='border: 1px solid #cbd5e1; border-radius: 30px; padding: 5px 5px 5px 20px; display: flex; align-items: center; margin-bottom: 25px;'>
                            
                            <input type='text' id='ws_url_input_modal' placeholder='Tìm nguồn mới trên web (URL, YouTube)...' style='flex: 1; border: none; outline: none; font-size: 15px; padding: 10px 0;'>
                            <button id='ws_btn_url_modal' style='background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 50%; width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #475569; margin-left: 10px; font-weight: bold;'>➔</button>
                        </div>
                        <div style='border: 1px dashed #cbd5e1; border-radius: 12px; padding: 40px 20px; text-align: center; background: #f8fafc;'>
                            <h3 style='margin: 0 0 10px 0; color: #475569; font-weight: 400;'>hoặc thả tệp của bạn</h3>
                            <p style='margin: 0 0 25px 0; color: #94a3b8; font-size: 13px;'>pdf, docx, txt, mp3, và các định dạng khác</p>
                            <label for='ws_file_upload_modal' class='ws-btn' style='padding: 10px 20px; background: white; border: 1px solid #e2e8f0; color: #334155; display: inline-flex; align-items: center; gap: 8px;'>
                                <span style='font-size: 16px;'>↑</span> Tải tệp lên
                            </label>
                            <input type='file' id='ws_file_upload_modal' style='display: none;'>
                        </div>
                        <div id='ws_modal_source_status' style='text-align: center; margin-top: 15px; color: #d97706; font-size: 14px; font-style: italic; min-height: 20px;'></div>
                    </div>
                </div>

                <div id='youtube_modal' style='display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.9); z-index:99999; justify-content:center; align-items:center;'>
                    <div style='position:relative; width:90%; max-width:850px; background:#000; border-radius:8px; overflow:visible;'>
                        
                        <button id='ws_youtube_modal_close'
                                style='position:absolute; top:-15px; right:-15px; width:35px; height:35px; background:#ef4444; color:white; border:none; border-radius:50%; cursor:pointer; font-size:20px; z-index:100001; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 10px rgba(0,0,0,0.3);'>
                            &times;
                        </button>

                        <div style='position:relative; padding-top:56.25%;'>
                            <iframe id='youtube_iframe' 
                                    style='position:absolute; top:0; left:0; width:100%; height:100%;' 
                                    frameborder='0' 
                                    allow='autoplay; encrypted-media' 
                                    allowfullscreen>
                            </iframe>
                        </div>
                    </div>
                </div>
                
                <div id='ws_mindmap_modal' class='ws-modal-overlay' style='display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:10000; justify-content:center; align-items:center;'>
                    <div style='background:#f1f5f9; width:90%; height:90%; border-radius:12px; display:flex; flex-direction:column; position:relative; overflow:hidden;'>
                        
                        <div style='padding:15px 20px; background:#fff; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;'>
                            <h3 style='margin:0; font-size:18px; color:#1e293b;'>Sơ đồ tư duy (Mindmap)</h3>
                            <div style='display:flex; gap:10px;'>
                                <button id='ws_mm_btn_generate' class='ws-btn' style='background:#10b981; color:#fff; border:none; padding:8px 15px; border-radius:4px; cursor:pointer;'>Tạo mới</button>
                                <button id='ws_mm_btn_save_img' class='ws-btn' style='background:#2563eb; color:#fff; border:none; padding:8px 15px; border-radius:4px; cursor:pointer; display:none;'>Lưu (PNG)</button>
                                <button id='ws_mindmap_modal_close' style='background:#64748b; color:#fff; border:none; width:32px; height:32px; border-radius:50%; cursor:pointer;'>&times;</button>
                            </div>
                        </div>

                        <div id='ws_mm_content_area' style='flex:1; overflow:auto; padding:40px; display:flex; justify-content:flex-start; align-items:flex-start;'>
                            <div id='ws_mm_draw_zone' style='background:#fff; padding:40px; border-radius:8px; min-width:fit-content; display:flex; flex-direction:row; align-items:center; margin: auto;'>
                                <p id='ws_mm_status' style='text-align:center; color:#64748b; font-style:italic;'>Đang tải...</p>
                            </div>
                        </div>

                    </div>
                </div>
                <script src='https://html2canvas.hertzen.com/dist/html2canvas.min.js'></script>

                <style>
                    /* Vùng vẽ chính - Khôi phục khung trắng */
                    #ws_mm_draw_zone {
                        display: inline-block;
                        min-width: 100%;
                        min-height: 100%;
                        position: relative;
                        background: #ffffff; /* Khôi phục màu trắng */
                        padding: 0; /* Để mm-layout quản lý padding */
                        border-radius: 12px;
                        box-shadow: 0 10px 25px rgba(0,0,0,0.05); /* Đổ bóng cho đẹp */
                        overflow: visible;
                    }

                    /* Layout chứa 3 cột - Là mốc tọa độ (0,0) cho dây nối */
                    .mm-layout { 
                        display: flex; 
                        flex-direction: row; 
                        align-items: flex-start; 
                        gap: 120px; /* Tăng khoảng cách để dây cong đẹp hơn */
                        position: relative; 
                        padding: 80px 100px; /* Khoảng đệm xung quanh sơ đồ */
                        min-width: max-content;
                    }
                    
                    .mm-col { 
                        display: flex; 
                        flex-direction: column; 
                        gap: 24px; 
                        position: relative;
                        min-width: 220px;
                        z-index: 2; /* Nằm trên dây nối */
                    }

                    .mm-node { 
                        padding: 14px 20px; 
                        background: #fff; 
                        border: 2px solid #2563eb; 
                        border-radius: 10px; 
                        width: 100%;
                        text-align: center; 
                        font-size: 13px; 
                        color: #1e293b; 
                        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
                        word-wrap: break-word;
                        box-sizing: border-box;
                        line-height: 1.5;
                    }

                    .mm-node.root { background: #2563eb; color: #fff; font-size: 15px; font-weight: bold; border: none; }
                    .mm-node.sub { border-color: #10b981; background: #f0fdf4; font-weight: 600; }
                    .mm-node.leaf { border-color: #94a3b8; font-size: 12px; text-align: left; background: #f8fafc; color: #475569; }
                </style>

                <div id='ws_source_detail_modal' class='ws-modal-overlay ws-modal-overlay-blur' style='display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1000; align-items: center; justify-content: center; background: rgba(0,0,0,0.4);'>
                    <div class='ws-modal-box' style='width: 90%; max-width: 650px; border-radius: 16px; padding: 30px; background: #ffffff; display: flex; flex-direction: column; max-height: 85vh;'>
                        
                        <div style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0;'>
                            <h3 style='margin: 0; font-size: 18px; color: #1e293b; font-family: sans-serif;'>Chi tiết tài liệu</h3>
                            <button id='ws_btn_close_source_detail_modal' style='background: none; border: none; font-size: 24px; cursor: pointer; color: #64748b; line-height: 1;'>&times;</button>
                        </div>

                        <div id='ws_source_loading' style='display: none; text-align: center; padding: 20px; color: #64748b; font-style: italic; font-size: 14px;'>
                            Đang tải dữ liệu...
                        </div>

                        <div id='ws_source_chunks_container' style='flex: 1; overflow-y: auto; padding-right: 10px; display: flex; flex-direction: column; gap: 15px;'>
                        </div>

                    </div>
                </div>
            </div>
        ";

        $output->addHTML( $html );
    }
}