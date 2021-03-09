<?php
/**
 * Flextensions_FlexGallery class.
 *
 * @category   Class
 * @package    Flextensions
 * @subpackage WordPress
 * @author     Felix Herzog
 * @copyright  2021 
 * @license    https://opensource.org/licenses/GPL-3.0 GPL-3.0-only
 * @link       link(https://github.com/fleks/flextensions-for-elementor,
 *             Flextensions for Elementor on GitHub)
 * @since      1.0.2
 * php version 7.1
 */

namespace Flextensions\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Utils;
use Elementor\Icons_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Core\Schemes\Typography;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Image_Size;
use Elementor\Core\Schemes\Color;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;

// Security Note: Blocks direct access to the plugin PHP files.
defined( 'ABSPATH' ) || die();

/**
 * Flextensions_FlexGallery widget class.
 *
 * @since 1.0.2
 */
class Flextensions_FlexGallery extends Widget_Base {
	/**
	 * Class constructor.
	 *
	 * @param array $data Widget data.
	 * @param array $args Widget arguments.
	 */
	 public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );

		wp_register_style( 'flextensions-flex-gallery', plugins_url( '/assets/css/flextensions-flex-gallery.css', FLEXTENSIONS ), array(), '1.0.0' );
		wp_register_script( 'flextensions-flex-gallery', plugins_url( '/assets/js/flextensions-flex-gallery.js', FLEXTENSIONS ), array(), '1.0.0' );
	}

	/**
	 * Retrieve the widget name.
	 *
	 * @since 1.0.2
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'flextensions-flex-gallery';
	}

	/**
	 * Retrieve the widget title.
	 *
	 * @since 1.0.2
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Flex Gallery', 'flextensions' );
	}

	/**
	 * Retrieve the widget icon.
	 *
	 * @since 1.0.2
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-toggle';
	}

	/**
	 * Retrieve the list of categories the widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * Note that currently Elementor supports only one category.
	 * When multiple categories passed, Elementor uses the first one.
	 *
	 * @since 1.0.2
	 *
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return array( 'flextensions-category' );
	}
	
	/**
	 * Enqueue styles.
	 */
	public function get_style_depends() {
		return array( 'flextensions-flex-gallery' );
	}

    /**
	 * Enqueue scripts.
	 */
	public function get_script_depends() {
		return array( 'flextensions-flex-gallery' );
	}

    /**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @since 1.1.0
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return [ 'icon list', 'icon', 'list' ];
	}    

	/**
	 * Register the widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.2
	 *
	 * @access protected
	 */
	protected function _register_controls() {

		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Content', 'elementor' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

        $repeater = new Repeater();

		$repeater->add_control(
			'image',
			[
				'label' => __( 'Choose Image', 'elementor' ),
				'type' => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
			]
		); 

		$repeater->add_control(
			'custom_dimension',
			[
				'label' => __( 'Image Dimension', 'elementor' ),
				'type' => Controls_Manager::IMAGE_DIMENSIONS,
				'description' => __( 'Crop the original image size to any custom size. Set custom width or height to keep the original size ratio.', 'plugin-name' ),
				'default' => [
					'width' => '',
					'height' => '',
				],
			]
		);

		$repeater->add_group_control(
			\Elementor\Group_Control_Image_Size::get_type(),
			[
				'name' => 'image_size', // // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `thumbnail_size` and `thumbnail_custom_dimension`.
				'exclude' => [ 'custom' ],
				'include' => [],
				'default' => 'large',
			]
		);		

		$repeater->add_control(
			'title',
            [
				'label' => __( 'Content', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => __( 'List Item', 'elementor' ),
				'default' => __( 'List Item', 'elementor' ),
                'dynamic' => [
					'active' => true,
				],
			]
		);

		$repeater->add_control(
			'link',
            [
				'label' => __( 'Link', 'elementor' ),
				'type' => \Elementor\Controls_Manager::URL,
				'placeholder' => 'https://your-link.com',
				'show_external' => true,
				'default' => [
					'url' => '#',
					'is_external' => false,
					'nofollow' => false,
				],
				'dynamic' => [
					'active' => true,
				],                
			]
		);

		$repeater->add_control(
			'css_class',
			[
				'label' => __( 'Link CSS Class', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'prefix_class' => '',
				'title' => __( 'Add your custom class WITHOUT the dot. e.g: my-class', 'elementor' ),
			]
		);

		$this->add_control(
			'list',
			[
				'label' => __( 'Buttons', 'elementor' ),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'title' =>  __( 'Image Title', 'flextensions' ),
					],
	    		],
				'title_field' => '{{{ title }}}',
		    ]
		);		



        $this->end_controls_section();

		$this->start_controls_section(
			'section_color',
			[
				'label' => __( 'Color', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        $this->start_controls_tabs( 'colors' );

		$this->start_controls_tab(
			'colors_normal',
			[
				'label' => __( 'Normal', 'elementor' ),
			]
		);
		
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'box_shadow',
				'label' => __( 'Box Shadow', 'elementor' ),
				'selector' => '{{WRAPPER}} a dt, {{WRAPPER}} a dd',
			]
		);	        

		$this->end_controls_tab();

		$this->start_controls_tab(
			  'colors_hover',
			  [
				  'label' => __( 'Hover', 'elementor' ),
			  ]
		);

	        $this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'box_shadow_hover',
				'label' => __( 'Box Shadow', 'elementor' ) . ' Hover',
				'selector' => '{{WRAPPER}} a.hover dt, {{WRAPPER}} a.hover dd',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_section();        

		$this->start_controls_section(
			'section_position',
			[
				'label' => __( 'Position', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'side_distance',
			[
				'label' => __( 'Distance Side', 'flextensions' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em' ],
				'range' => [
					'px' => [
						'min' => -50,
						'max' => 200,
					],
					'%' => [
						'min' => -5,
						'max' => 100,
					],
					'em' => [
						'min' => -5,
						'max' => 100,
						'step' => 0.1,
					],	
				],
				'default' => [
					'unit' => 'px',
					'size' => 0,
				],
				'selectors' => [
					'{{WRAPPER}}' => '{{side.value}}: calc( {{SIZE}}{{UNIT}} - {{width.size}}{{width.unit}} )',
				],
			]
		);

		$this->add_control(
			'show_text',
			[
				'label' => __( 'Show Text - ignore Distance Side', 'flextensions' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'flextensions' ),
				'label_off' => __( 'Hide', 'flextensions' ),
    			'default' => 'no',
				'selectors' => [
					'{{WRAPPER}}' => '{{side.VALUE}}: 0px;',
				],
			]
		);        

		$this->add_responsive_control(
			'top',
			[
				'label' => __( 'Distance Top', 'flextensions' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vh' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
						'step' => 1,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
					'vh' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => '',
				],
				'selectors' => [
					'{{WRAPPER}}' => 'top: {{SIZE}}{{UNIT}};',
				],
                'condition' => [
                    'bottom[size]' => '',
                ],                
			]
		);

		$this->add_responsive_control(
			'bottom',
			[
				'label' => __( 'Distance Bottom', 'flextensions' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vh' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
						'step' => 1,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
					'vh' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => '',
				],
				'selectors' => [
					'{{WRAPPER}}' => 'bottom: {{SIZE}}{{UNIT}};',
				],
                'condition' => [
                    'top[size]' => '',
                ],
			]
		);
        
		$this->add_control(
			'position_anchor_top',
			[
				'label' => __( 'Position Anchor', 'flextensions' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Middle', 'flextensions' ),
				'label_off' => __( 'Top', 'flextensions' ),
    			'default' => 'no',
				'selectors' => [
					'{{WRAPPER}}' => 'transform: translate(0, -50%)',
				],
                'condition' => [
                    'top[size]!' => '',
                ],                
			]
		); 
        
		$this->add_control(
			'position_anchor_bottom',
			[
				'label' => __( 'Position Anchor', 'flextensions' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Middle', 'flextensions' ),
				'label_off' => __( 'Bottom', 'flextensions' ),
    			'default' => 'no',
				'selectors' => [
					'{{WRAPPER}}' => 'transform: translate(0, 50%)',
				],
                'condition' => [
                    'bottom[size]!' => '',
                ],                
			]
		);           

		$this->add_control(
			'z_index',
			[
				'label' => __( 'Z-Index', 'elementor' ),
				'type' => Controls_Manager::NUMBER,
				'min' => 0,
				'step' => 1,
				'default' => 10,
				'selectors' => [
					'{{WRAPPER}}' => 'z-index: {{VALUE}};',
				],
			]
		);

        $this->end_controls_section();

		$this->start_controls_section(
			'dimension_section',
			[
			  'label' => __( 'Dimension', 'elementor' ),
			  'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label' => __( 'Icon Size', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
					'em' => [
						'min' => 0,
						'max' => 100,
                        'step' => 0.1,
					],
					'rm' => [
						'min' => 0,
						'max' => 100,
                        'step' => 0.1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 32,
				],
				'selectors' => [
					'{{WRAPPER}} dt' => 'font-size: {{SIZE}}{{UNIT}}; text-align: center;',
				],
			]
		);

    	$this->add_responsive_control(
			'icon_padding',
			[
				'label' => __( 'Icon Padding ', 'flextensions' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
					'em' => [
						'min' => 0,
						'max' => 100,
						'step' => 0.1,
					],	
				],
				'default' => [
					'unit' => 'px',
					'size' => 0,
				],
				'selectors' => [
					'{{WRAPPER}} dt i' => 'padding: {{SIZE}}{{UNIT}}',
				],
			]
		);
        
    	$this->add_responsive_control(
			'icon_container_width',
			[
				'label' => __( 'Icon Container Width', 'flextensions' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
					'em' => [
						'min' => 0,
						'max' => 100,
						'step' => 0.1,
					],	
				],
				'default' => [
					'unit' => 'px',
					'size' => 42,
				],
				'selectors' => [
					'{{WRAPPER}} dt' => 'width: {{SIZE}}{{UNIT}}',
				],
			]
		);

        $this->add_responsive_control(
			'height',
			[
				'label' => __( 'Height', 'flextensions' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
					'em' => [
						'min' => 0,
						'max' => 100,
						'step' => 0.1,
					],	
				],
				'default' => [
					'unit' => 'px',
					'size' => 42,
				],
				'selectors' => [
                    '{{WRAPPER}} dt, {{WRAPPER}} dd' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'content_typography',
				'label' => __( 'Typography', 'elementor' ),
				'scheme' => Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} a dd',
			]
		);

		$this->add_responsive_control(
			'width',
			[
				'label' => __( 'Width of Text', 'flextensions' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 500,
						'step' => 1,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
					'vw' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 5,
				],                
				'selectors' => [
					'{{WRAPPER}} dd' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

    	$this->add_responsive_control(
			'text_padding',
			[
				'label' => __( 'Text Padding', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%', 'rem'],
				'selectors' => [
					'{{WRAPPER}} dd' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);            
        
    	$this->add_responsive_control(
			'space_between',
			[
				'label' => __( 'Space Between Horizontal', 'flextensions' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
					'em' => [
						'min' => 0,
						'max' => 100,
						'step' => 0.1,
					],	
				],
				'default' => [
					'unit' => 'px',
					'size' => 5,
				],
				'selectors' => [
					'{{WRAPPER}} dl > a:not(:last-of-type) dt, {{WRAPPER}} dl > a:not(:last-of-type) dd' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				],
			]
		);
        
    	$this->add_responsive_control(
			'margin',
			[
				'label' => __( 'Space Between Vertical', 'flextensions' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
					'em' => [
						'min' => 0,
						'max' => 100,
						'step' => 0.1,
					],	
				],
				'default' => [
					'unit' => 'px',
					'size' => '',
				],
				'selectors' => [
					'{{WRAPPER}} img' => 'margin: {{SIZE}}{{UNIT}};',
				],
			]
		);        

		$this->add_control(
			'border_color',
			[
				'label' => __( 'Primary Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'global' => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'selectors' => [
					'{{WRAPPER}} dt' => 'border-{{side.value}}-color: {{VALUE}}',
				],
                'condition' => [
                    'border_space_between!' => '',
                ],             
			]
		);        

		$this->add_control(
			'border_radius',
			[
				'label' => __( 'Border Radius', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} dt' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'animation_section',
			[
			  'label' => __( 'Animation', 'elementor' ),
			  'tab' => Controls_Manager::TAB_STYLE,
			]
		);

    	$this->add_control(
			'transition',
			[
				'label' => __( 'Transition', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'ms','s' ],
				'range' => [
					'ms' => [
						'min' => 0,
						'max' => 5000,
						'step' => 100,
					],
                's' => [
						'min' => 0,
						'max' => 10,
						'step' => 0.1,
					],	
				],
				'default' => [
					'unit' => 'ms',
					'size' => 500,
				],
				'selectors' => [
					'{{WRAPPER}} dt, {{WRAPPER}} dd' => 'transition: {{side.VALUE}} {{SIZE}}{{UNIT}}',
				],
			]
		);        
		
		$this->add_control(
			'hover_animation',
			[
				'label' => __( 'Hover Animation', 'elementor' ),
				'type' => Controls_Manager::HOVER_ANIMATION,
            ]
        );

		$this->end_controls_section();

	}

	/**
	 * Render the widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.2
	 *
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( $settings['list'] ) {
			foreach (  $settings['list'] as $item ) {
				echo \Elementor\Group_Control_Image_Size::get_attachment_image_html( $item, 'image_size', 'image' );
			}
		}
	}

	/**
	 * Render the widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 1.0.2
	 *
	 * @access protected
	 */
	protected function _content_template() {
		?>
		<# _.each( settings.list, function( item ) {
		var image = {
			id: item.image.id,
			url: item.image.url,
			size: item.image_size,
			dimension: item.image_custom_dimension,
			model: view.getEditModel()
		};
		var image_url = elementor.imagesManager.getImageUrl( image );
		#>
		<img src="{{{ image_url }}}" {{{ image.size }}}/>	
		<# }); #>
	<?php
	}
}