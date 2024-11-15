<?php
/**
 * Schedule.
 */

namespace WLOPTF\Field;

/**
 * Class.
 */
class Schedule {

    /**
     * ID.
     */
    protected $id;

    /**
     * Placeholder.
     */
    protected $placeholder;

    /**
     * Name.
     */
    protected $name;

    /**
     * Temporary name.
     */
    protected $tname;

    /**
     * Value.
     */
    protected $value;

    /**
     * Default.
     */
    protected $default;

    /**
     * Base name.
     */
    protected $base_name;

    /**
     * Base data.
     */
    protected $base_data;

    /**
     * Temporary base name.
     */
    protected $tbase_name;

    /**
     * Args.
     */
    protected $args;

    /**
     * Store.
     */
    protected $store;

    /**
     * class.
     */
    protected $class;

    /**
     * attributes.
     */
    protected $attributes;

    /**
     * Constructor.
     */
    public function __construct( $args = array(), $store = true ) {
        if ( ! is_array( $args ) ) {
            return;
        }

        $args = wp_parse_args( $args, array(
            'id'          => '',
            'placeholder' => '',
            'default'     => '',
            'class'       => '',
            'attributes'  => '',
            'base_name'   => '',
            'base_data'   => array(),
            'tbase_name'  => '',
        ) );

        $id          = ( isset( $args['id'] ) ? wloptf_cast( $args['id'], 'key' ) : '' );
        $placeholder = ( isset( $args['placeholder'] ) ? wloptf_cast( $args['placeholder'], 'text' ) : '' );
        $default     = ( isset( $args['default'] ) ? wloptf_cast( $args['default'], 'array' ) : '' );
        $class       = ( isset( $args['class'] ) ? wloptf_cast( $args['class'], 'text' ) : '' );
        $attributes  = ( isset( $args['attributes'] ) ? wloptf_cast( $args['attributes'], 'array' ) : array() );
        $base_name   = ( isset( $args['base_name'] ) ? wloptf_cast( $args['base_name'], 'text' ) : '' );
        $base_data   = ( isset( $args['base_data'] ) ? wloptf_cast( $args['base_data'], 'array' ) : array() );
        $tbase_name  = ( isset( $args['tbase_name'] ) ? wloptf_cast( $args['tbase_name'], 'text' ) : '' );

        $store = wloptf_cast( $store, 'bool' );

        if ( empty( $id ) ) {
            return;
        }

        $this->id          = $id;
        $this->placeholder = $placeholder;
        $this->default     = $default;
        $this->class       = $class;
        $this->attributes  = $attributes;
        $this->base_name   = $base_name;
        $this->base_data   = $base_data;
        $this->tbase_name  = $tbase_name;
        $this->args        = $args;
        $this->store       = $store;

        $this->prepare_name();
        $this->prepare_value();

        $this->render_field();
    }

    /**
     * Prepare name.
     */
    protected function prepare_name() {
        if ( true === $this->store ) {
            $this->name = sprintf( '%1$s[%2$s]', $this->base_name, $this->id );
        }

        if ( ! empty( $this->tbase_name ) ) {
            $this->tname = sprintf( '%1$s[%2$s]', $this->tbase_name, $this->id );
        }
    }

    /**
     * Prepare value.
     */
    protected function prepare_value() {
        $this->value = ( isset( $this->base_data[ $this->id ] ) ? wloptf_cast( $this->base_data[ $this->id ], 'array' ) : $this->default );
    }

    /**
     * Get attributes.
     */
    protected function get_attributes() {
        $atts = '';

        $class = $this->class;
        $attrs = $this->attributes;

        $class = ( ( 0 < strlen( $class ) ) ? ( 'wloptf-schedule-wrapper ' . $class ) : 'wloptf-schedule-wrapper' );
        $atts .= ( ( 0 < strlen( $class ) && ! isset( $attrs['class'] ) ) ? sprintf( 'class="%1$s"', $class ) : '' );

        foreach ( $attrs as $attr_key => $attr_value ) {
            if ( 'class' === $attr_key && 0 < strlen( $class ) ) {
                $attr_value = ( ( 0 < strlen( $attr_value ) ) ? ( $class . ' ' . $attr_value ) : $class );
            }

            $attr = sprintf( '%1$s="%2$s"', $attr_key, $attr_value );
            $atts .= ( ( 0 < strlen( $atts ) ) ? ( ' ' . $attr ) : $attr );
        }

        return $atts;
    }

    /**
     * Render field.
     */
    protected function render_field() {
        $duration_name = sprintf( '%1$s[%2$s]', $this->name, 'duration' );
        $duration_tname = sprintf( '%1$s[%2$s]', $this->tname, 'duration' );

        $unit_name = sprintf( '%1$s[%2$s]', $this->name, 'unit' );
        $unit_tname = sprintf( '%1$s[%2$s]', $this->tname, 'unit' );

        $duration_value = ( isset( $this->value['duration'] ) ? wloptf_cast( $this->value['duration'], 'int' ) : '' );
        $unit_value = ( isset( $this->value['unit'] ) ? wloptf_cast( $this->value['unit'], 'key' ) : '' );

        $duration_add_attr = ( ! empty( $duration_name ) ? ' name="' . esc_attr( $duration_name ) . '"' : '' );
        $duration_add_attr .= ( ! empty( $duration_tname ) ? ' data-wloptf-tname="' . esc_attr( $duration_tname ) . '"' : '' );

        $unit_add_attr = ( ! empty( $unit_name ) ? ' name="' . esc_attr( $unit_name ) . '"' : '' );
        $unit_add_attr .= ( ! empty( $unit_tname ) ? ' data-wloptf-tname="' . esc_attr( $unit_tname ) . '"' : '' );

        $attrs = $this->get_attributes();
        $add_attr = ( ! empty( $attrs ) ? ( ' ' . $attrs ) : '' );

        $unit_options = array(
            'days'    => esc_html__( 'Days', 'woolentor-pro' ),
            'hours'   => esc_html__( 'Hours', 'woolentor-pro' ),
            'minutes' => esc_html__( 'Minutes', 'woolentor-pro' ),
            'seconds' => esc_html__( 'Seconds', 'woolentor-pro' ),
        );
        ?>
        <div <?php echo wp_kses_data( trim( $add_attr ) ); ?>>
            <div class="wloptf-schedule-duration">
                <input type="number" value="<?php echo esc_attr( $duration_value ); ?>" <?php echo wp_kses_data( trim( $duration_add_attr ) ); ?>>
            </div>
            <div class="wloptf-schedule-unit">
                <select <?php echo wp_kses_data( trim( $unit_add_attr ) ); ?>>
                    <?php
                    foreach ( $unit_options as $unit_key => $unit_label ) {
                        if ( $unit_key === $unit_value ) {
                            ?>
                            <option value="<?php echo esc_attr( $unit_key ); ?>" selected><?php echo esc_html( $unit_label ); ?></option>
                            <?php
                        } else {
                            ?>
                            <option value="<?php echo esc_attr( $unit_key ); ?>"><?php echo esc_html( $unit_label ); ?></option>
                            <?php
                        }
                    }
                    ?>
                </select>
            </div>
        </div>
        <?php
    }

    /**
     * Instance.
     */
    public static function instance( $args = array(), $store = true ) {
        new self( $args, $store );
    }

}