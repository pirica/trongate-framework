<?php
class Dashboard extends Trongate {

    public function index() {
        $data['view_module'] = 'dashboard';
        $data['view_file'] = 'dashboard';
        $this->templates->members_area($data);
    }

}