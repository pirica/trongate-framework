<?php
/**
 * Default homepage class serving as the entry point for public website access.
 * Renders the initial landing page as configured in the framework settings.
 */
class Welcome extends Trongate {

    /**
     * Renders the (default) homepage for public access.
     *
     * @return void
     */
    public function index(): void {

        $additional_includes_top[] = '<script src="js/trongate-mx.min.js"></script>';
        $additional_includes_btm[] = '<script src="code_generator_module/js/code-generator.js"></script>';

        $data = [
            'additional_includes_top' => $additional_includes_top,
            'additional_includes_btm' => $additional_includes_btm,
            'view_module' => 'welcome',
            'view_file' => 'default_homepage'
        ];

        $this->templates->public($data);
    }

}