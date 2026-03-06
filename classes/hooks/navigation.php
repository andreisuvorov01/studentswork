<?php
namespace local_studentworks\hooks;

defined('MOODLE_INTERNAL') || die();

class navigation {
    public static function extend_primary_navigation(\core\hook\navigation\primary_navigation_extend $hook): void {
        $nav = $hook->get_navigation();
        $nav->add(
            get_string('pluginname', 'local_studentworks'),
            new \moodle_url('/local/studentworks/index.php'),
            \navigation_node::TYPE_CUSTOM,
            null,
            'local_studentworks',
            new \pix_icon('i/assign', '')
        );
    }
}
