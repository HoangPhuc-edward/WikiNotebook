<?php
namespace MyNotebook\Pages;

class DashboardPage {
    public function render( $output ) {
        // Tạo URL an toàn của MediaWiki
        $specialPageUrl = \SpecialPage::getTitleFor( 'MyNotebook' )->getFullURL();
        $llmManagerUrl = \SpecialPage::getTitleFor( 'MyNotebook' )->getFullURL( [ 'action' => 'llm_manager' ] );
        $templateManagerUrl = \SpecialPage::getTitleFor( 'MyNotebook' )->getFullURL( [ 'action' => 'template_manager' ] );
        $sourceManagerUrl = \SpecialPage::getTitleFor( 'MyNotebook' )->getFullURL( [ 'action' => 'source_manager' ] );
        
        // Truyền Base URL của trang Special xuống JS
        $output->addJsConfigVars( 'MyNotebookBaseUrl', $specialPageUrl );

        $html = "
           <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
            
            <style>
            
                a.ws-btn-outline, 
                a.ws-btn-outline:visited {
                    padding: 10px 20px; 
                    background: #ffffff; 
                    color: #187A35 !important; 
                    border: 2px solid #187A35; 
                    text-decoration: none !important; 
                    border-radius: 25px; 
                    font-weight: bold; 
                    font-size: 14px; 
                    display: inline-flex; 
                    align-items: center; 
                    gap: 8px;
                    transition: all 0.2s ease-in-out;
                }
                
                a.ws-btn-outline i {
                    color: #187A35 !important;
                    transition: color 0.2s ease-in-out;
                }
                
                a.ws-btn-outline:hover, 
                a.ws-btn-outline:active,
                a.ws-btn-outline:focus {
                    background: #187A35 !important;
                    color: #ffffff !important;
                    text-decoration: none !important;
                }
                
                a.ws-btn-outline:hover i {
                    color: #ffffff !important;
                }
                
                /* 3 & 4. Xử lý font chữ và đường kẻ ngang mặc định của thẻ H2 MediaWiki */
                h2.ws-dashboard-title {
                    font-family: sans-serif !important; /* Ép font không chân */
                    border-bottom: none !important; /* Xóa đường kẻ ngang khó chịu */
                    margin: 0 0 20px 0 !important; 
                    color: #1e293b !important; 
                    text-align: left; 
                    font-size: 1.6em;
                }
            </style>

            <div style='font-family: sans-serif; padding: 10px 0;'>
                
                <div style='display: flex; gap: 15px; border-bottom: 2px solid #e2e8f0; padding-bottom: 20px; margin-bottom: 25px; flex-wrap: wrap;'>
                    
                    <a href='{$templateManagerUrl}' class='ws-btn-outline'>
                        <i class='fas fa-file-code'></i> Cài đặt dàn ý
                    </a>
                    <a href='{$sourceManagerUrl}' class='ws-btn-outline'>
                        <i class='fas fa-database'></i> Cài đặt nguồn
                    </a>
                </div>

                <h2 class='ws-dashboard-title'>Danh sách sổ tay</h2>

                <div id='notebook-grid' style='display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 20px; margin-right: 40px;'>
                    <p style='color: #666;'>Đang tải dữ liệu...</p>
                </div>

            </div>
        ";

        $output->addHTML( $html );
    }
}