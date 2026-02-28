<?php
class Code_generator extends Trongate {

    private $api_base_url = 'http://localhost/trongate_v2_dev/';

    /**
     * Outputs the "Open Code Generator" trigger element.
     *
     * If a custom HTML string is provided via the $html parameter, it will be used.
     * Otherwise, the default view 'code_generator_trigger' will be rendered and returned as a string.
     *
     * @param string|null $html Optional HTML string to use as the trigger element.
     * @return void Outputs the trigger element directly.
     */
    public function draw_open_code_generator(?string $html = null): void {
        block_url('code_generator/draw_open_code_generator');
        $data['api_base_url'] = $this->api_base_url;
        $trigger_el = (isset($html)) ? $html : $this->view('code_generator_trigger', $data, true);
        echo $trigger_el;
    }

    /**
     * List all module directories
     *
     * Returns a JSON array of all module directory names. This endpoint is only
     * accessible when the application is running in development mode.
     *
     * @return void
     */
    public function list_mods(): void {
        if (strtolower(ENV) !== 'dev') {
            http_response_code(403);
            echo 'This endpoint is only available when in development mode.';
            die();
        }
        $all_modules = $this->get_directories('modules', true);
        http_response_code(200);
        echo json_encode($all_modules);
    }

    /**
     * Get all directories within a specified subdirectory of APPPATH
     *
     * @param string $subdirectory The subdirectory path relative to APPPATH
     * @param bool $names_only If true, return only directory names; if false, return full paths (default: false)
     * @return array An array of directory paths or names found within the target directory
     */
    private function get_directories(string $subdirectory, bool $names_only = false): array {
        $target_path = APPPATH . $subdirectory;
        $directories = [];
        if (is_dir($target_path)) {
            $items = scandir($target_path);

            foreach ($items as $item) {
                // Skip current and parent directory references
                if ($item === '.' || $item === '..') {
                    continue;
                }

                $full_path = $target_path . '/' . $item;

                // Check if it's a directory
                if (is_dir($full_path)) {
                    $directories[] = $names_only ? $item : $full_path;
                }
            }
        }
        return $directories;
    }

}
