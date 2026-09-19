<?php

class oFim_Backend
{

    private $_menuSlug = 'phimtop1-manager';
    private $_page = '';

    public function __construct()
    {
        (new oFim_Permalink())->register();
        if (isset($_GET['page'])) $this->_page = $_GET['page'];
        add_action('admin_menu', array($this, 'menus'));
        if (isset($_GET['page'])) {
            if ($_GET['page'] == 'phimtop1-manager-crawl-phimtop1') {
                add_action('admin_enqueue_scripts', array($this, 'css'));
            }
        }
        add_action('admin_enqueue_scripts', array($this, 'codemirror_enqueue_scripts'));
    }


    public function codemirror_enqueue_scripts($hook)
    {
        $cm_settings['codeEditor'] = wp_enqueue_code_editor(array('type' => 'text/css'));
        wp_localize_script('jquery', 'cm_settings', $cm_settings);
    }

    public function css()
    {
        wp_enqueue_style('admin_css', OFIM_CSS_URL . '/style.css', false, '');
    }

    public function menus()
    {

        add_menu_page('PHIMTOP1', 'Cài đặt PHIMTOP1', 'manage_options', $this->_menuSlug, array($this, 'dispatch_function'), '', 3);
        add_submenu_page($this->_menuSlug, 'Crawl PhimTop1', 'Crawl PhimTop1', 'manage_options', $this->_menuSlug . '-crawl-phimtop1', array($this, 'dispatch_function'));
    }

    public function dispatch_function()
    {
        $page = $this->_page;
        global $oController;
        if ($page == 'phimtop1-manager-crawl-phimtop1') {
            $obj = $oController->getController('AdminCrawlPhimTop1', '/backend');
            $obj->display();
        }
        if ($page == 'phimtop1-manager') {
            $obj = $oController->getController('AdminManager', '/backend');
            $obj->display();
        }
    }


}