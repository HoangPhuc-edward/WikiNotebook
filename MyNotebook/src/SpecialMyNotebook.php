<?php
namespace MyNotebook;

use SpecialPage;

class SpecialMyNotebook extends SpecialPage {
    public function __construct() {
        parent::__construct( 'MyNotebook' );
    }

    public function execute( $par ) {
        $this->setHeaders();
        $request = $this->getRequest();
        $output = $this->getOutput();

        // Đưa URL API vào JavaScript để các script có thể sử dụng
        global $wgMyNotebookApiUrl;
        $output->addJsConfigVars( 'MyNotebookApiUrl', $wgMyNotebookApiUrl );
        $output->addModules( 'ext.myNotebook.scripts' );

        // Tiêu đề chung cho toàn bộ Extension
        $output->setPageTitle( "Sổ tay nông nghiệp" );

        // ---------------------------------------------------------
        // GHI ĐÈ CSS CỦA MEDIAWIKI ĐỂ MỞ RỘNG GIAO DIỆN FULL MÀN HÌNH
        // ---------------------------------------------------------
        $output->addInlineStyle('
            /* Phá vỡ lớp bọc ngoài cùng mà bạn vừa tìm thấy */
            main#content.mw-body {
                display: block !important; /* Chìa khóa để phá lưới */
                max-width: 100% !important;
                padding: 20px 25px !important; /* Tạo lề ngoài cùng cho đẹp */
            }

            .firstHeading.mw-first-heading {
                font-size: 28px;
            }

            /* 2. Ép tất cả các lớp bọc ngoài cùng bung rộng 100% */
            .mw-page-container,
            .mw-page-container-inner,
            .mw-content-container {
                max-width: 100% !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            /* 3. Ẩn hẳn cột chứa các công cụ linh tinh bên tay phải (nếu có) */
            .vector-column-end {
                display: none !important;
            }
        ');;

        // Đọc tham số 'action' trên URL (Mặc định là 'dashboard')
        $action = $request->getVal( 'action', 'dashboard' );

        // Dựa vào action để gọi class tương ứng trong thư mục Pages
        // Đã sắp xếp lại 'default' xuống cuối để code an toàn và chuẩn xác hơn
        switch ( $action ) {
            case 'workspace':
                $notebookId = $request->getInt( 'id', 0 );
                $page = new \MyNotebook\Pages\WorkspacePage();
                $page->render( $output, $notebookId );
                break;
                
            case 'llm_manager':
                $page = new \MyNotebook\Pages\LLMManagerPage();
                $page->render( $output );
                break;
            
            case 'template_manager':
                $page = new \MyNotebook\Pages\TemplateManagerPage();
                $page->render( $output );
                break;

            case 'source_manager':
                $page = new \MyNotebook\Pages\SourcePage();
                $page->render( $output );
                break;
                
            case 'dashboard':
            default:
                $page = new \MyNotebook\Pages\DashboardPage();
                $page->render( $output );
                break;
        }
    }
}