<?php
namespace MyNotebook\Pages;

use MyNotebook\I18n;

class DashboardPage {
    public function render( $output ) {
        // Create a safe MediaWiki URL
        $specialPageUrl = \SpecialPage::getTitleFor( 'MyNotebook' )->getFullURL();
        $llmManagerUrl = \SpecialPage::getTitleFor( 'MyNotebook' )->getFullURL( [ 'action' => 'llm_manager' ] );
        $templateManagerUrl = \SpecialPage::getTitleFor( 'MyNotebook' )->getFullURL( [ 'action' => 'template_manager' ] );
        $sourceManagerUrl = \SpecialPage::getTitleFor( 'MyNotebook' )->getFullURL( [ 'action' => 'source_manager' ] );
        $lang = I18n::getLocale();
        
        // Pass the Special page Base URL down to JS
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
                
                /* 3 & 4. Handle MediaWiki H2 default font and horizontal rule */
                h2.ws-dashboard-title {
                    font-family: sans-serif !important; /* Force sans-serif font */
                    border-bottom: none !important; /* Remove the annoying horizontal rule */
                    margin: 0 0 20px 0 !important;
                    color: #1e293b !important;
                    text-align: left;
                    font-size: 1.6em;
                }

                /* Language switcher */
                .ws-lang-switcher { position: relative; }

                .ws-lang-btn {
                    padding: 10px 16px;
                    background: #ffffff;
                    color: #334155;
                    border: 2px solid #cbd5e1;
                    border-radius: 25px;
                    font-weight: bold;
                    font-size: 14px;
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    cursor: pointer;
                    transition: all 0.2s ease-in-out;
                }
                .ws-lang-btn:hover { border-color: #187A35; color: #187A35; }
                .ws-lang-btn i.fa-chevron-down { font-size: 11px; transition: transform 0.2s ease-in-out; }
                .ws-lang-switcher.open .ws-lang-btn i.fa-chevron-down { transform: rotate(180deg); }

                .ws-lang-menu {
                    display: none;
                    position: absolute;
                    top: calc(100% + 6px);
                    left: 0;
                    background: #ffffff;
                    border: 1px solid #e2e8f0;
                    border-radius: 10px;
                    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
                    padding: 6px;
                    min-width: 180px;
                    z-index: 50;
                }
                .ws-lang-switcher.open .ws-lang-menu { display: block; }

                .ws-lang-option {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    width: 100%;
                    padding: 9px 10px;
                    border: none;
                    background: none;
                    border-radius: 6px;
                    font-size: 14px;
                    color: #334155;
                    text-align: left;
                    cursor: pointer;
                }
                .ws-lang-option:hover { background: #f1f5f9; }
                .ws-lang-option.active { background: #ecfdf5; color: #187A35; font-weight: bold; }
                .ws-lang-option .ws-lang-flag { font-size: 16px; }
                .ws-lang-option .fa-check { margin-left: auto; color: #187A35; }
            </style>

            <div style='font-family: sans-serif; padding: 10px 0;'>

                <div style='display: flex; gap: 15px; border-bottom: 2px solid #e2e8f0; padding-bottom: 20px; margin-bottom: 25px; flex-wrap: wrap;'>

                    <a href='{$templateManagerUrl}' class='ws-btn-outline'>
                        <i class='fas fa-file-code'></i> " . I18n::msg( 'dashboard.outline_settings' ) . "
                    </a>
                    <a href='{$sourceManagerUrl}' class='ws-btn-outline'>
                        <i class='fas fa-database'></i> " . I18n::msg( 'dashboard.source_settings' ) . "
                    </a>

                    <div id='ws_lang_switcher' class='ws-lang-switcher'>
                        <button type='button' id='ws_lang_toggle' class='ws-lang-btn' title='" . I18n::msg( 'dashboard.select_language' ) . "'>
                            <i class='fas fa-globe'></i>
                            <span id='ws_lang_current_label'>" . ( $lang === 'vi' ? 'Tiếng Việt' : 'English' ) . "</span>
                            <i class='fas fa-chevron-down'></i>
                        </button>
                        <div id='ws_lang_menu' class='ws-lang-menu'>
                            <button type='button' class='ws-lang-option" . ( $lang === 'vi' ? ' active' : '' ) . "' data-lang='vi'>
                                <span class='ws-lang-flag'>🇻🇳</span> Tiếng Việt
                                " . ( $lang === 'vi' ? "<i class='fas fa-check'></i>" : '' ) . "
                            </button>
                            <button type='button' class='ws-lang-option" . ( $lang === 'en' ? ' active' : '' ) . "' data-lang='en'>
                                <span class='ws-lang-flag'>🇬🇧</span> English
                                " . ( $lang === 'en' ? "<i class='fas fa-check'></i>" : '' ) . "
                            </button>
                        </div>
                    </div>
                </div>

                <h2 class='ws-dashboard-title'>" . I18n::msg( 'dashboard.notebook_list' ) . "</h2>

                <div id='notebook-grid' style='display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 20px; margin-right: 40px;'>
                    <p style='color: #666;'>" . I18n::msg( 'common.loading_data' ) . "</p>
                </div>

            </div>
        ";

        $output->addHTML( $html );
    }
}
