<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Jaroncito_ElementAddons_Sample_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'sample_widget';
    }

    public function get_title() {
        return __( 'Sample Widget', 'jaroncito-elementaddons' );
    }

    public function get_icon() {
        return 'eicon-code';
    }

    public function get_categories() {
        return [ 'general' ];
    }

    public function get_script_depends() {
        return [ 'jaroncito-elementaddons-js' ];
    }

    public function get_style_depends() {
        return [ 'jaroncito-elementaddons-css' ];
    }

    protected function _register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __( 'Content', 'jaroncito-elementaddons' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'title',
            [
                'label' => __( 'Title', 'jaroncito-elementaddons' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __( 'Hello World', 'jaroncito-elementaddons' ),
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        echo '<h2>' . esc_html( $settings['title'] ) . '</h2>';
    }

    protected function _content_template() {
        ?>
        <h2>{{{ settings.title }}}</h2>
        <?php
    }
}
