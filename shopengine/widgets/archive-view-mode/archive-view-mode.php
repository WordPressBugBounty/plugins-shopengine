<?php

namespace Elementor;

use ShopEngine\Widgets\Products;

defined('ABSPATH') || exit;


class ShopEngine_Archive_View_Mode extends \ShopEngine\Base\Widget
{

	const SELECTOR_PREFIX = '.shopengine-archive-products.shopengine-archive-products--view-list ';
	const CL_SELECTOR_PREFIX = '.shopengine-archive-products.shopengine-archive-products--view-list.shopengine-archive-products--custom-list .shopengine-cl-content ';

	public function config() {
		return new ShopEngine_Archive_View_Mode_Config();
	}

	protected function register_controls() {
		$this->start_controls_section(
			'shopengine_view_mode_section',
			[
				'label' => __('View Mode', 'shopengine'),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'shopengine_view_mode_four_grid',
			[
				'label' => esc_html__( 'Show Four Grid', 'shopengine' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'shopengine' ),
				'label_off' => esc_html__( 'Hide', 'shopengine' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);
		

		$this->add_control(
			'shopengine_view_mode_four_grid_icon',
			[
				'label' => esc_html__( 'Four Grid Icon', 'shopengine' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'separator' => 'after',
				'default' => [
					'value' => 'shopengine-icon shopengine-icon-grid-1',
					'library' => 'shopengine-icons',
				],
				'condition' => [
					'shopengine_view_mode_four_grid' => 'yes',
				],
			]
		);

		$this->add_control(
			'shopengine_view_mode_three_grid',
			[
				'label' => esc_html__( 'Show Three Grid', 'shopengine' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'shopengine' ),
				'label_off' => esc_html__( 'Hide', 'shopengine' ),
				'return_value' => 'yes',
				'default' => '',
			]
		);

		$this->add_control(
			'shopengine_view_mode_three_grid_icon',
			[
				'label' => esc_html__( 'Three Grid Icon', 'shopengine' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'separator' => 'after',
				'default' => [
					'value' => 'eicon-gallery-grid',
					'library' => 'eicon',
				],
				'condition' => [
					'shopengine_view_mode_three_grid' => 'yes',
				],
			]
		);

		$this->add_control(
			'shopengine_view_mode_two_grid',
			[
				'label' => esc_html__( 'Show Two Grid', 'shopengine' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'shopengine' ),
				'label_off' => esc_html__( 'Hide', 'shopengine' ),
				'return_value' => 'yes',
				'default' => '',
			]
		);

		$this->add_control(
			'shopengine_view_mode_two_grid_icon',
			[
				'label' => esc_html__( 'Two Grid Icon', 'shopengine' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'separator' => 'after',
				'default' => [
					'value' => 'shopengine-icon shopengine-icon-grid-3',
					'library' => 'shopengine-icons',
				],
				'condition' => [
					'shopengine_view_mode_two_grid' => 'yes',
				],
			]
		);

		$this->add_control(
			'shopengine_view_mode_list_grid',
			[
				'label' => esc_html__( 'Show List Grid', 'shopengine' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'shopengine' ),
				'label_off' => esc_html__( 'Hide', 'shopengine' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'shopengine_view_mode_list_grid_icon',
			[
				'label' => esc_html__( 'List Icon', 'shopengine' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'default' => [
					'value' => 'shopengine-icon shopengine-icon-grid-2',
					'library' => 'shopengine-icons',
				],
				'condition' => [
					'shopengine_view_mode_list_grid' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'shopengine_cl_section',
			[
				'label'     => esc_html__('List View: Custom Layout', 'shopengine'),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => [
					'shopengine_view_mode_list_grid' => 'yes',
				],
			]
		);

		$this->add_control(
			'shopengine_cl_show_badge',
			[
				'label'        => esc_html__('Show Sale Badge', 'shopengine'),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__('Show', 'shopengine'),
				'label_off'    => esc_html__('Hide', 'shopengine'),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'shopengine_cl_show_category',
			[
				'label'        => esc_html__('Show Category', 'shopengine'),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__('Show', 'shopengine'),
				'label_off'    => esc_html__('Hide', 'shopengine'),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'shopengine_cl_category_max',
			[
				'label'     => esc_html__('Max Categories', 'shopengine'),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				// Matches the fixed render cap in shopengine_render_list_custom_content()
				// (archive-products/screens/default.php) that this control's CSS enforces.
				'max'       => 10,
				'default'   => 1,
				'condition' => [
					'shopengine_cl_show_category' => 'yes',
				],
			]
		);

		$this->add_control(
			'shopengine_cl_show_rating',
			[
				'label'        => esc_html__('Show Rating', 'shopengine'),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__('Show', 'shopengine'),
				'label_off'    => esc_html__('Hide', 'shopengine'),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'shopengine_cl_title_tag',
			[
				'label'     => esc_html__('Title HTML Tag', 'shopengine'),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'Div',
					'span' => 'Span',
					'p'    => 'P',
				],
				'default'   => 'h2',
			]
		);

		$this->add_control(
			'shopengine_cl_show_off_tag',
			[
				'label'        => esc_html__('Show Off Percentage', 'shopengine'),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__('Show', 'shopengine'),
				'label_off'    => esc_html__('Hide', 'shopengine'),
				'return_value' => 'yes',
				'default'      => 'yes',
				// Show/hide is driven by CSS (not the PHP render gate the other cl_show_* toggles
				// use) so it live-updates in the editor instantly, same as the style controls below
				// — see [[reference-elementor-live-preview]].
				'selectors'    => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-price .shopengine-cl-off-tag' => 'display: inline-block;',
				],
			]
		);

		$this->add_control(
			'shopengine_cl_show_excerpt',
			[
				'label'        => esc_html__('Show Description', 'shopengine'),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__('Show', 'shopengine'),
				'label_off'    => esc_html__('Hide', 'shopengine'),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'shopengine_cl_excerpt_length',
			[
				'label'     => esc_html__('Max Words', 'shopengine'),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 50,
				'step'      => 1,
				'default'   => 20,
				'condition' => [
					'shopengine_cl_show_excerpt' => 'yes',
				],
			]
		);

		$this->add_control(
			'shopengine_cl_add_to_cart_text',
			[
				'label'       => esc_html__('Add to Cart Text', 'shopengine'),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__('Add to cart', 'shopengine'),
			]
		);

		$cl_ordering_repeater = new Repeater();
		$this->add_control(
			'shopengine_cl_ordering_list',
			[
				'label'        => esc_html__('Content Order', 'shopengine'),
				'type'         => Controls_Manager::REPEATER,
				'fields'       => $cl_ordering_repeater->get_controls(),
				'default'      => [
					[
						'list_title' => esc_html__('Category', 'shopengine'),
						'list_key'   => 'category',
					],
					[
						'list_title' => esc_html__('Rating', 'shopengine'),
						'list_key'   => 'rating',
					],
					[
						'list_title' => esc_html__('Title', 'shopengine'),
						'list_key'   => 'title',
					],
					[
						'list_title' => esc_html__('Price', 'shopengine'),
						'list_key'   => 'price',
					],
					[
						'list_title' => esc_html__('Description', 'shopengine'),
						'list_key'   => 'excerpt',
					],
					[
						'list_title' => esc_html__('Add to Cart', 'shopengine'),
						'list_key'   => 'add-to-cart',
					],
				],
				'title_field'  => '{{{ list_title }}}',
				'item_actions' => [
					'add'       => false,
					'duplicate' => false,
					'remove'    => false,
					'sort'      => true,
				],
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'shopengine_cl_show_quickview',
			[
				'label'        => esc_html__('Show Quick View', 'shopengine'),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__('Show', 'shopengine'),
				'label_off'    => esc_html__('Hide', 'shopengine'),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'shopengine_cl_show_wishlist',
			[
				'label'        => esc_html__('Show Wishlist', 'shopengine'),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__('Show', 'shopengine'),
				'label_off'    => esc_html__('Hide', 'shopengine'),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'shopengine_cl_show_comparison',
			[
				'label'        => esc_html__('Show Comparison', 'shopengine'),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__('Show', 'shopengine'),
				'label_off'    => esc_html__('Hide', 'shopengine'),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'shopengine_cl_show_direct_checkout',
			[
				'label'        => esc_html__('Show Direct Checkout', 'shopengine'),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__('Show', 'shopengine'),
				'label_off'    => esc_html__('Hide', 'shopengine'),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$cl_action_btns_ordering_repeater = new Repeater();
		$this->add_control(
			'shopengine_cl_action_btns_ordering_list',
			[
				'label'        => esc_html__('Add to Cart / Icons Order', 'shopengine'),
				'type'         => Controls_Manager::REPEATER,
				'fields'       => $cl_action_btns_ordering_repeater->get_controls(),
				'default'      => [
					[
						'list_title' => esc_html__('Quick View', 'shopengine'),
						'list_key'   => 'quick-view',
					],
					[
						'list_title' => esc_html__('Wishlist', 'shopengine'),
						'list_key'   => 'wishlist',
					],
					[
						'list_title' => esc_html__('Add to Cart', 'shopengine'),
						'list_key'   => 'add-to-cart',
					],
					[
						'list_title' => esc_html__('Comparison', 'shopengine'),
						'list_key'   => 'comparison',
					],
					[
						'list_title' => esc_html__('Direct Checkout (PRO)', 'shopengine'),
						'list_key'   => 'direct-checkout',
					],
				],
				'title_field'  => '{{{ list_title }}}',
				'item_actions' => [
					'add'       => false,
					'duplicate' => false,
					'remove'    => false,
					'sort'      => true,
				],
				'separator'    => 'before',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'shopengine_section_style',
			[
				'label' => esc_html__('View Mode Button', 'shopengine'),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'shopengine_view_mode_icon_size',
			[
				'label'      => esc_html__('Icon Size (px)', 'shopengine'),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range'      => array(
					'px' => array(
						'min' => 1,
						'max' => 100,
					),
				),
				'default'    => [
					'unit' => 'px',
					'size' => 18,
				],
				'selectors'  => [
					'{{WRAPPER}} .shopengine-archive-view-mode .shopengine-archive-view-mode-switch-list .shopengine-archive-view-mode-switch :is(svg, i)' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; font-size: {{SIZE}}{{UNIT}}; flex-shrink: 0;',
				],
			]
		);

		$this->add_control(
			'shopengine_view_mode_icon_box_size',
			[
				'label'      => esc_html__('Icon Box Size (px)', 'shopengine'),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range'      => array(
					'px' => array(
						'min' => 1,
						'max' => 100,
					),
				),
				'default'    => [
					'unit' => 'px',
					'size' => 52,
				],
				'selectors'  => [
					'{{WRAPPER}} .shopengine-archive-view-mode .shopengine-archive-view-mode-switch-list .shopengine-archive-view-mode-switch' => 'height: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'shopengine_view_mode_alignment',
			[
				'label'     => esc_html__('Alignment', 'shopengine'),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'start'   => [
						'description' => esc_html__('Left', 'shopengine'),
						'icon'        => 'eicon-text-align-left',
					],
					'center' => [
						'description' => esc_html__('Center', 'shopengine'),
						'icon'        => 'eicon-text-align-center',
					],
					'flex-end'  => [
						'description' => esc_html__('Right', 'shopengine'),
						'icon'        => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .shopengine-archive-view-mode .shopengine-archive-view-mode-switch-list' => 'justify-content: {{VALUE}};',
					'.rtl {{WRAPPER}}.elementor-view-align-start .shopengine-archive-view-mode .shopengine-archive-view-mode-switch-list' => 'justify-content: start;',  
					'.rtl {{WRAPPER}}.elementor-view-align-flex-end .shopengine-archive-view-mode .shopengine-archive-view-mode-switch-list' => 'justify-content: end;',
				],
				'prefix_class'  => 'elementor-view-align-',
			]
		);

		$this->start_controls_tabs('shopengine_view_mode_tabs_style');

		$this->start_controls_tab(
			'shopengine_view_mode_tabnormal',
			[
				'label' => esc_html__('Normal', 'shopengine'),
			]
		);

		$this->add_control(
			'shopengine_view_mode_color',
			[
				'label'     => esc_html__('Color', 'shopengine'),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#a7a7a7',
				'alpha'		=> false,
				'selectors' => [
					'{{WRAPPER}} .shopengine-archive-view-mode .shopengine-archive-view-mode-switch-list .shopengine-archive-view-mode-switch' => 'color: {{VALUE}};',
					'{{WRAPPER}} .shopengine-archive-view-mode .shopengine-archive-view-mode-switch-list .shopengine-archive-view-mode-switch svg path' => 'stroke: {{VALUE}}; fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'shopengine_view_mode_background',
			[
				'label'     => esc_html__('Background Color', 'shopengine'),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#fff',
				'alpha'		=> false,
				'selectors' => [
					'{{WRAPPER}} .shopengine-archive-view-mode .shopengine-archive-view-mode-switch-list .shopengine-archive-view-mode-switch' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'shopengine_view_mode_tabhover',
			[
				'label' => esc_html__('Hover & Active', 'shopengine'),
			]
		);

		$this->add_control(
			'shopengine_view_mode_color_hover',
			[
				'label'     => esc_html__('Color', 'shopengine'),
				'type'      => Controls_Manager::COLOR,
				'alpha'		=> false,
				'selectors' => [
					'{{WRAPPER}} .shopengine-archive-view-mode .shopengine-archive-view-mode-switch-list .shopengine-archive-view-mode-switch:hover'    => 'color: {{VALUE}};',
					'{{WRAPPER}} .shopengine-archive-view-mode .shopengine-archive-view-mode-switch-list .shopengine-archive-view-mode-switch.isactive' => 'color: {{VALUE}};',
					'{{WRAPPER}} .shopengine-archive-view-mode .shopengine-archive-view-mode-switch-list .shopengine-archive-view-mode-switch:hover svg path' => 'stroke: {{VALUE}};transition:all 0.3s ease-in-out',
					'{{WRAPPER}} .shopengine-archive-view-mode .shopengine-archive-view-mode-switch-list .shopengine-archive-view-mode-switch.isactive svg path' => 'stroke: {{VALUE}};',
				],
				'default'   => '#ff3f00',
			]
		);

		$this->add_control(
			'shopengine_view_mode_background_hover',
			[
				'label'     => esc_html__('Background Color', 'shopengine'),
				'type'      => Controls_Manager::COLOR,
				'alpha'		=> false,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .shopengine-archive-view-mode .shopengine-archive-view-mode-switch-list .shopengine-archive-view-mode-switch:hover'    => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .shopengine-archive-view-mode .shopengine-archive-view-mode-switch-list .shopengine-archive-view-mode-switch.isactive' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'           => 'shopengine_view_mode_border',
				'label'          => esc_html__('Border', 'shopengine'),
				'fields_options' => [
					'border' => [
						'default' => 'solid',
					],
					'width'  => [
						'default' => [
							'top'      => '1',
							'right'    => '1',
							'bottom'   => '1',
							'left'     => '1',
							'isLinked' => true,
						],
						'selectors' => [
							'{{WRAPPER}} .shopengine-archive-view-mode .shopengine-archive-view-mode-switch-list .shopengine-archive-view-mode-switch' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
							'.rtl {{WRAPPER}} .shopengine-archive-view-mode .shopengine-archive-view-mode-switch-list .shopengine-archive-view-mode-switch' => 'border-width: {{TOP}}{{UNIT}} {{LEFT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{RIGHT}}{{UNIT}}',
						]
					],
					'color'  => [
						'default' => '#f2f2f2'
					]
				],
				'selector'       => '{{WRAPPER}} .shopengine-archive-view-mode .shopengine-archive-view-mode-switch-list .shopengine-archive-view-mode-switch',
				'separator'	=> 'before'
			]
		);

		$this->end_controls_section();

		/**
		 * 
		 * 
		 * 
		 * Product Layout: List View Image style
		 * 
		 * 
		 */ 
		$this->start_controls_section(
			'shopengine_product_layout',
			[
				'label' => esc_html__('List View: Image Style', 'shopengine'),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'shopengine_view_mode_list_grid' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'shopengine_product_layout_gap',
			[
				'label'      => esc_html__('Image gap from conent', 'shopengine'),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range'      => array(
					'px' => array(
						'min' => 1,
						'max' => 200,
					),
				),
				'default'    => [
					'unit' => 'px',
					'size' => 60,
				],

				'selectors'  => [
					self::SELECTOR_PREFIX . '.shopengine-archive-mode-grid' => 'column-gap: {{SIZE}}{{UNIT}};',
					'.shopengine-archive-products.shopengine-archive-products--view-list.shopengine-archive-products--custom-list .shopengine-archive-products__left-image' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'shopengine_product_image_width',
			[
				'label'      => esc_html__('Image Width', 'shopengine'),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range'      => array(
					'px' => array(
						'min' => 1,
						'max' => 800,
					),
				),
				'default'    => [
					'unit' => 'px',
					'size' => 400,
				],

				'selectors'  => [
					self::SELECTOR_PREFIX . '.shopengine-archive-products__left-image img' => 'width: {{SIZE}}{{UNIT}} !important; min-width: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'shopengine_product_image_height',
			[
				'label'      => esc_html__('Image Height', 'shopengine'),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range'      => array(
					'px' => array(
						'min' => 1,
						'max' => 800,
					),
				),
				'default'    => [
					'unit' => 'px',
					'size' => 400,
				],

				'selectors'  => [
					self::SELECTOR_PREFIX . '.shopengine-archive-products__left-image img' => 'height: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'shopengine_product_image_fit',
			[
				'label'   => esc_html__('Image Fit', 'shopengine'),
				'type'    => Controls_Manager::SELECT,
				'default' => 'cover',
				'options' => [
					'cover'    => esc_html__('Cover', 'shopengine'),
					'contain'  => esc_html__('Contain', 'shopengine'),
					'fill'     => esc_html__('Fill', 'shopengine')
				],
				'selectors' => [
					self::SELECTOR_PREFIX . '.shopengine-archive-products__left-image img' => 'object-fit: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'shopengine_product_image_position',
			[
				'label'   => esc_html__('Image View Position', 'shopengine'),
				'type'    => Controls_Manager::SELECT,
				'default' => 'center',
				'options' => [
					'top'     => esc_html__('Top', 'shopengine'),
					'center'  => esc_html__('Center', 'shopengine'),
					'bottom'  => esc_html__('Bottom', 'shopengine')
				],
				'selectors'  => [
					self::SELECTOR_PREFIX . '.shopengine-archive-products__left-image img' => 'object-position: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'shopengine_product_image_margin',
			[
				'label'      => esc_html__('Margin', 'shopengine'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px'],
				'selectors'  => [
					self::SELECTOR_PREFIX . '.shopengine-archive-products__left-image img' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'.rtl ' . self::SELECTOR_PREFIX . '.shopengine-archive-products__left-image img' => 'margin: {{TOP}}{{UNIT}} {{LEFT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{RIGHT}}{{UNIT}};',
				],
				'separator'  => 'before',
			]
		);

		$this->end_controls_section(); // end of Product Layout: List View

		/**
		 * 
		 * 
		 * 
		 * List View Content Style
		 * 
		 * 
		 */ 
		$this->start_controls_section(
			'shopengine_product_content',
			[
				'label' => esc_html__('List View: Content Style', 'shopengine'),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'shopengine_view_mode_list_grid' => 'yes',
				],
			]
		);

		$this->add_control(
			'shopengine_product_content_bg_color',
			[
				'label'     => esc_html__('Background Color', 'shopengine'),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => false,
				'selectors' => [
					self::SELECTOR_PREFIX . '.product, '
						. self::CL_SELECTOR_PREFIX => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'shopengine_product_content_gap',
			[
				'label'      => esc_html__('Content gap from buttons', 'shopengine'),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range'      => array(
					'px' => array(
						'min' => 1,
						'max' => 200,
					),
				),
				'default'    => [
					'unit' => 'px',
					'size' => 25,
				],

				'selectors'  => [
					self::SELECTOR_PREFIX . '.woocommerce-LoopProduct-link' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart' => 'margin-top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'shopengine_product_content_padding',
			[
				'label'      => esc_html__('Padding', 'shopengine'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px'],
				'selectors'  => [
					self::SELECTOR_PREFIX . '.product, '
						. self::CL_SELECTOR_PREFIX => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
					'.rtl .shopengine-archive-products.shopengine-archive-products--view-list .product, '
						. '.rtl ' . self::CL_SELECTOR_PREFIX => 'padding: {{TOP}}{{UNIT}} {{LEFT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{RIGHT}}{{UNIT}} !important;',
				],
				'separator'  => 'before',
			]
		);

		$this->end_controls_section(); // end of List View Content Style


		/*
			=============================
			product title start
			=============================
		*/

		$this->start_controls_section(
			'shopengine_section_style_title',
			[
				'label' => esc_html__('List View : Product Title', 'shopengine'),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'shopengine_view_mode_list_grid' => 'yes',
				],
			]
		);
		

		$this->add_control(
			'shopengine_title_color',
			[
				'label'     => esc_html__('Color', 'shopengine'),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => false,
				'default'   => '#101010',
				'selectors' => [
					self::SELECTOR_PREFIX . 'ul.products li.product .woocommerce-loop-product__title, '
						. self::CL_SELECTOR_PREFIX . '.shopengine-cl-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'           => 'shopengine_title_color_typography',
				'label'          => esc_html__('Typography', 'shopengine'),
				'selector'       => self::SELECTOR_PREFIX . 'ul.products li.product .woocommerce-loop-product__title, '
					. self::CL_SELECTOR_PREFIX . '.shopengine-cl-title',
				'exclude'        => ['font_family', 'letter_spacing', 'text_decoration', 'font_style'],
				'fields_options' => [
					'typography'     => [
						'default' => 'custom',
					],
					'font_weight'    => [
						'default' => '500',
					],
					'font_size'      => [
						'label'      => esc_html__('Font Size (px)', 'shopengine'),
						'default'    => [
							'size' => '16',
							'unit' => 'px',
						],
						'size_units' => ['px'],
					],
					'text_transform' => [
						'default' => 'capitalize',
					],
					'line_height'    => [
						'label'      => esc_html__('Line Height (px)', 'shopengine'),
						'default'    => [
							'size' => '18',
							'unit' => 'px',
						],
						'size_units' => ['px'] // enable only px
					],
				],
			]
		);


		$this->add_responsive_control(
			'shopengine_title_padding',
			[
				'label'      => esc_html__('Padding', 'shopengine'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px'],
				'selectors'  => [
					self::SELECTOR_PREFIX . '.product .woocommerce-loop-product__title, '
						. self::CL_SELECTOR_PREFIX . '.shopengine-cl-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
					'.rtl .shopengine-archive-products.shopengine-archive-products--view-list .product .woocommerce-loop-product__title, '
						. '.rtl ' . self::CL_SELECTOR_PREFIX . '.shopengine-cl-title' => 'padding: {{TOP}}{{UNIT}} {{LEFT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{RIGHT}}{{UNIT}} !important;',
				],
				'separator'  => 'before',
			]
		);
		$this->end_controls_section(); // end of product title

		/*
			=============================
			List View: Custom Layout - Sale Badge style
			=============================
		*/
		$this->start_controls_section(
			'shopengine_cl_section_style_badge',
			[
				'label'     => esc_html__('List View: Sale Badge', 'shopengine'),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'shopengine_view_mode_list_grid' => 'yes',
					'shopengine_cl_show_badge'       => 'yes',
				],
			]
		);

		$this->add_control(
			'shopengine_cl_badge_color',
			[
				'label'     => esc_html__('Color', 'shopengine'),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => false,
				'default'   => '#FFFFFF',
				'selectors' => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-badge .onsale' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'shopengine_cl_badge_bg_color',
			[
				'label'     => esc_html__('Background Color', 'shopengine'),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => false,
				'default'   => '#4285F4',
				'selectors' => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-badge .onsale' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'shopengine_cl_badge_typography',
				'label'    => esc_html__('Typography', 'shopengine'),
				'selector' => self::CL_SELECTOR_PREFIX . '.shopengine-cl-badge .onsale',
				'exclude'  => ['font_family'],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'shopengine_cl_badge_border',
				'selector' => self::CL_SELECTOR_PREFIX . '.shopengine-cl-badge .onsale',
			]
		);

		$this->add_control(
			'shopengine_cl_badge_radius',
			[
				'label'      => esc_html__('Border Radius', 'shopengine'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'default'    => [
					'top'      => '50',
					'right'    => '50',
					'bottom'   => '50',
					'left'     => '50',
					'unit'     => '%',
					'isLinked' => true,
				],
				'selectors'  => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-badge .onsale' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section(); // end of List View: Custom Layout - Sale Badge style

		/*
			=============================
			List View: Custom Layout - Category style
			=============================
		*/
		$this->start_controls_section(
			'shopengine_cl_section_style_category',
			[
				'label'     => esc_html__('List View: Category', 'shopengine'),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'shopengine_view_mode_list_grid' => 'yes',
					'shopengine_cl_show_category'    => 'yes',
				],
			]
		);

		$this->add_control(
			'shopengine_cl_category_color',
			[
				'label'     => esc_html__('Color', 'shopengine'),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => false,
				'default'   => '#858585',
				'selectors' => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-category' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'shopengine_cl_category_typography',
				'label'    => esc_html__('Typography', 'shopengine'),
				'selector' => self::CL_SELECTOR_PREFIX . '.shopengine-cl-category',
				'exclude'  => ['font_family'],
			]
		);

		$this->add_responsive_control(
			'shopengine_cl_category_margin',
			[
				'label'      => esc_html__('Margin', 'shopengine'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px'],
				'selectors'  => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-category' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'.rtl ' . self::CL_SELECTOR_PREFIX . '.shopengine-cl-category' => 'margin: {{TOP}}{{UNIT}} {{LEFT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{RIGHT}}{{UNIT}};',
				],
				'separator'  => 'before',
			]
		);

		$this->end_controls_section(); // end of List View: Custom Layout - Category style

		/*
			=============================
			List View: Custom Layout - Rating style
			=============================
		*/
		$this->start_controls_section(
			'shopengine_cl_section_style_rating',
			[
				'label'     => esc_html__('List View: Rating', 'shopengine'),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'shopengine_view_mode_list_grid' => 'yes',
					'shopengine_cl_show_rating'      => 'yes',
				],
			]
		);

		$this->add_control(
			'shopengine_cl_rating_color',
			[
				'label'     => esc_html__('Color', 'shopengine'),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => false,
				'default'   => '#FEC42D',
				'selectors' => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-rating .star-rating' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'shopengine_cl_rating_empty_color',
			[
				'label'     => esc_html__('Empty Star Color', 'shopengine'),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => false,
				'default'   => '#cfc8d8',
				'selectors' => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-rating .star-rating::before' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'shopengine_cl_rating_count_color',
			[
				'label'     => esc_html__('Review Count Color', 'shopengine'),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => false,
				'selectors' => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-rating .shopengine-product-rating-review-count' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'shopengine_cl_rating_size',
			[
				'label'      => esc_html__('Font Size (px)', 'shopengine'),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 11,
				],
				'selectors'  => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-rating .star-rating, ' . self::CL_SELECTOR_PREFIX . '.shopengine-cl-rating .shopengine-product-rating-review-count' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'shopengine_cl_rating_gap',
			[
				'label'      => esc_html__('Star Gap (px)', 'shopengine'),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'selectors'  => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-rating .star-rating' => 'letter-spacing: {{SIZE}}{{UNIT}}; width: calc(5.4em + (4 * {{SIZE}}{{UNIT}}));',
					'.rtl ' . self::CL_SELECTOR_PREFIX . '.shopengine-cl-rating .star-rating' => 'letter-spacing: {{SIZE}}{{UNIT}}; width: calc(5.4em + (4 * {{SIZE}}{{UNIT}}));',
				],
			]
		);

		$this->add_responsive_control(
			'shopengine_cl_rating_margin',
			[
				'label'      => esc_html__('Margin', 'shopengine'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px'],
				'selectors'  => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-rating' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'.rtl ' . self::CL_SELECTOR_PREFIX . '.shopengine-cl-rating' => 'margin: {{TOP}}{{UNIT}} {{LEFT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{RIGHT}}{{UNIT}};',
				],
				'separator'  => 'before',
			]
		);

		$this->end_controls_section(); // end of List View: Custom Layout - Rating style

		/*
			=============================
			List View: Custom Layout - Price style
			=============================
		*/
		$this->start_controls_section(
			'shopengine_cl_section_style_price',
			[
				'label'     => esc_html__('List View: Price', 'shopengine'),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'shopengine_view_mode_list_grid' => 'yes',
				],
			]
		);

		$this->add_control(
			'shopengine_cl_price_color',
			[
				'label'     => esc_html__('Color', 'shopengine'),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => false,
				'default'   => '#101010',
				'selectors' => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-price .amount' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'shopengine_cl_price_typography',
				'label'    => esc_html__('Typography', 'shopengine'),
				'selector' => self::CL_SELECTOR_PREFIX . '.shopengine-cl-price .amount',
				'exclude'  => ['font_family'],
			]
		);

		$this->add_responsive_control(
			'shopengine_cl_price_margin',
			[
				'label'      => esc_html__('Margin', 'shopengine'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px'],
				'selectors'  => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-price' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'.rtl ' . self::CL_SELECTOR_PREFIX . '.shopengine-cl-price' => 'margin: {{TOP}}{{UNIT}} {{LEFT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{RIGHT}}{{UNIT}};',
				],
				'separator'  => 'before',
			]
		);

		$this->end_controls_section(); // end of List View: Custom Layout - Price style

		/*
			=============================
			List View: Custom Layout - Off Tag style
			=============================
		*/
		$this->start_controls_section(
			'shopengine_cl_section_style_off_tag',
			[
				'label'     => esc_html__('List View: Off Tag', 'shopengine'),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'shopengine_view_mode_list_grid' => 'yes',
					'shopengine_cl_show_off_tag'     => 'yes',
				],
			]
		);

		$this->add_control(
			'shopengine_cl_off_tag_color',
			[
				'label'     => esc_html__('Color', 'shopengine'),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => false,
				'default'   => '#FFFFFF',
				'selectors' => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-price .shopengine-cl-off-tag' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'shopengine_cl_off_tag_bg_color',
			[
				'label'     => esc_html__('Background', 'shopengine'),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => false,
				'default'   => '#F54F29',
				'selectors' => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-price .shopengine-cl-off-tag' => 'background: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'shopengine_cl_off_tag_padding',
			[
				'label'      => esc_html__('Padding', 'shopengine'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px'],
				'default'    => [
					'top'      => '0',
					'right'    => '10',
					'bottom'   => '0',
					'left'     => '10',
					'unit'     => 'px',
					'isLinked' => false,
				],
				'selectors'  => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-price .shopengine-cl-off-tag' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'.rtl ' . self::CL_SELECTOR_PREFIX . '.shopengine-cl-price .shopengine-cl-off-tag' => 'padding: {{TOP}}{{UNIT}} {{LEFT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{RIGHT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'shopengine_cl_off_tag_radius',
			[
				'label'      => esc_html__('Border Radius', 'shopengine'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px'],
				'default'    => [
					'top'      => '4',
					'right'    => '4',
					'bottom'   => '4',
					'left'     => '4',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors'  => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-price .shopengine-cl-off-tag' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'.rtl ' . self::CL_SELECTOR_PREFIX . '.shopengine-cl-price .shopengine-cl-off-tag' => 'border-radius: {{TOP}}{{UNIT}} {{LEFT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{RIGHT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section(); // end of List View: Custom Layout - Off Tag style

		/*
			=============================
			List View: Custom Layout - Description style
			=============================
		*/
		$this->start_controls_section(
			'shopengine_cl_section_style_excerpt',
			[
				'label'     => esc_html__('List View: Description', 'shopengine'),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'shopengine_view_mode_list_grid' => 'yes',
					'shopengine_cl_show_excerpt'     => 'yes',
				],
			]
		);

		$this->add_control(
			'shopengine_cl_excerpt_color',
			[
				'label'     => esc_html__('Color', 'shopengine'),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => false,
				'selectors' => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-excerpt' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'shopengine_cl_excerpt_typography',
				'label'    => esc_html__('Typography', 'shopengine'),
				'selector' => self::CL_SELECTOR_PREFIX . '.shopengine-cl-excerpt',
				'exclude'  => ['font_family'],
			]
		);

		$this->add_responsive_control(
			'shopengine_cl_excerpt_margin',
			[
				'label'      => esc_html__('Margin', 'shopengine'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px'],
				'selectors'  => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-excerpt' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'.rtl ' . self::CL_SELECTOR_PREFIX . '.shopengine-cl-excerpt' => 'margin: {{TOP}}{{UNIT}} {{LEFT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{RIGHT}}{{UNIT}};',
				],
				'separator'  => 'before',
			]
		);

		$this->end_controls_section(); // end of List View: Custom Layout - Description style

		/*
			=============================
			List View: Add to Cart
			=============================
		*/
		$this->start_controls_section(
			'shopengine_cl_section_style_cart',
			[
				'label'     => esc_html__('List View: Add to Cart', 'shopengine'),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'shopengine_view_mode_list_grid' => 'yes',
				],
			]
		);

		$cl_cart_btn_sel = 'a.add_to_cart_button, a.product_type_variable, a.product_type_grouped, a.product_type_external';

		$this->add_control(
			'shopengine_cl_cart_btn_bg_clr',
			[
				'label'     => esc_html__('Background Color', 'shopengine'),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#4361EE',
				'selectors' => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_cart_btn_sel . ')' => 'background: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'shopengine_cl_cart_btn_hbg_clr',
			[
				'label'     => esc_html__('Hover Background Color', 'shopengine'),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#354EC9',
				'selectors' => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_cart_btn_sel . '):hover' => 'background: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'shopengine_cl_cart_btn_clr',
			[
				'label'     => esc_html__('Text/Icon Color', 'shopengine'),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_cart_btn_sel . ')' => 'color: {{VALUE}} !important;',
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_cart_btn_sel . ') :is(i, span, svg, path)' => 'color: {{VALUE}} !important; fill: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'shopengine_cl_cart_btn_hover_clr',
			[
				'label'     => esc_html__('Hover Text/Icon Color', 'shopengine'),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_cart_btn_sel . '):hover' => 'color: {{VALUE}} !important;',
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_cart_btn_sel . '):hover :is(i, span, svg, path)' => 'color: {{VALUE}} !important; fill: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'shopengine_cl_cart_btn_typography',
				'label'    => esc_html__('Typography', 'shopengine'),
				'selector' => self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_cart_btn_sel . ')',
				'exclude'  => ['font_family'],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'           => 'shopengine_cl_cart_btn_border',
				'fields_options' => [
					'border' => [],
					'width'  => [
						'label'   => esc_html__('Border Width', 'shopengine'),
						'default' => [
							'top'    => '0',
							'right'  => '0',
							'bottom' => '0',
							'left'   => '0',
						],
					],
					'color'  => [
						'label' => esc_html__('Border Color', 'shopengine'),
					],
				],
				'selector' => self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_cart_btn_sel . ')',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'           => 'shopengine_cl_cart_btn_border_hover',
				'fields_options' => [
					'border' => [
						'label' => esc_html__('Hover Border Type', 'shopengine'),
					],
					'width'  => [
						'label'   => esc_html__('Border Width', 'shopengine'),
						'default' => [
							'top'    => '0',
							'right'  => '0',
							'bottom' => '0',
							'left'   => '0',
						],
					],
					'color'  => [
						'label' => esc_html__('Border Color', 'shopengine'),
					],
				],
				'selector' => self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_cart_btn_sel . '):hover',
			]
		);

		$this->add_responsive_control(
			'shopengine_cl_cart_btn_radius',
			[
				'label'      => esc_html__('Border Radius', 'shopengine'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'default'    => [
					'top'      => '50',
					'right'    => '50',
					'bottom'   => '50',
					'left'     => '50',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors'  => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_cart_btn_sel . ')' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'shopengine_cl_cart_btn_width',
			[
				'label'      => esc_html__('Width', 'shopengine'),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => ['px', '%'],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 400,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_cart_btn_sel . ')' => 'width: {{SIZE}}{{UNIT}} !important; flex: none;',
				],
			]
		);

		$this->add_responsive_control(
			'shopengine_cl_cart_btn_padding',
			[
				'label'      => esc_html__('Padding', 'shopengine'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px'],
				'default'    => [
					'top'      => '14',
					'right'    => '28',
					'bottom'   => '14',
					'left'     => '28',
					'unit'     => 'px',
					'isLinked' => false,
				],
				'selectors'  => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_cart_btn_sel . ')' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
					'.rtl ' . self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_cart_btn_sel . ')' => 'padding: {{TOP}}{{UNIT}} {{LEFT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{RIGHT}}{{UNIT}} !important;',
				],
				'separator'  => 'before',
			]
		);

		$this->add_responsive_control(
			'shopengine_cl_cart_btn_margin',
			[
				'label'      => esc_html__('Margin', 'shopengine'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px'],
				'selectors'  => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_cart_btn_sel . ')' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'.rtl ' . self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_cart_btn_sel . ')' => 'margin: {{TOP}}{{UNIT}} {{LEFT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{RIGHT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'           => 'shopengine_cl_cart_btn_box_shadow',
				'fields_options' => [
					'box_shadow' => [
						'default' => [
							'horizontal' => 0,
							'vertical'   => 1,
							'blur'       => 3,
							'spread'     => 0,
							'color'      => 'rgba(0,0,0,0.06)',
						],
					],
				],
				'selector' => self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_cart_btn_sel . ')',
			]
		);

		$this->end_controls_section(); // end of List View: Add to Cart

		/*
			=============================
			List View: Action Buttons
			=============================
		*/
		$this->start_controls_section(
			'shopengine_cl_section_style_action_btns',
			[
				'label'     => esc_html__('List View: Action Buttons', 'shopengine'),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'shopengine_view_mode_list_grid' => 'yes',
				],
			]
		);

		$cl_action_btn_sel = '.shopengine-wishlist, .shopengine-comparison, .shopengine-quickview-trigger, .shopengine_direct_checkout';

		$this->add_control(
			'shopengine_cl_action_btn_bg_clr',
			[
				'label'     => esc_html__('Background Color', 'shopengine'),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_action_btn_sel . ')' => 'background: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'shopengine_cl_action_btn_hover_active_bg_clr',
			[
				'label'     => esc_html__('Hover and Active Background Color', 'shopengine'),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_action_btn_sel . '):hover' => 'background: {{VALUE}} !important;',
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_action_btn_sel . ').active' => 'background: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'shopengine_cl_action_btn_clr',
			[
				'label'     => esc_html__('Icon Color', 'shopengine'),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#101010',
				'selectors' => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_action_btn_sel . ')' => 'color: {{VALUE}} !important;',
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_action_btn_sel . ') :is(i, span, svg, path)' => 'color: {{VALUE}} !important; fill: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'shopengine_cl_action_btn_hover_active_clr',
			[
				'label'     => esc_html__('Hover and Active Color', 'shopengine'),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#F03D3F',
				'selectors' => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_action_btn_sel . '):hover' => 'color: {{VALUE}} !important;',
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_action_btn_sel . '):hover :is(i, span, svg, path)' => 'color: {{VALUE}} !important; fill: {{VALUE}} !important;',
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_action_btn_sel . ').active' => 'color: {{VALUE}} !important;',
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_action_btn_sel . ').active :is(i, span, svg, path)' => 'color: {{VALUE}} !important; fill: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'           => 'shopengine_cl_action_btn_border',
				'fields_options' => [
					'border' => [
						'default' => 'solid',
					],
					'width'  => [
						'label'   => esc_html__('Border Width', 'shopengine'),
						'default' => [
							'top'    => '1',
							'right'  => '1',
							'bottom' => '1',
							'left'   => '1',
						],
					],
					'color'  => [
						'label'   => esc_html__('Border Color', 'shopengine'),
						'default' => '#f2f2f2',
					],
				],
				'selector' => self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_action_btn_sel . ')',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'           => 'shopengine_cl_action_btn_border_hover',
				'fields_options' => [
					'border' => [
						'label'   => esc_html__('Hover Border Type', 'shopengine'),
						'default' => 'solid',
					],
					'width'  => [
						'label'   => esc_html__('Border Width', 'shopengine'),
						'default' => [
							'top'    => '1',
							'right'  => '1',
							'bottom' => '1',
							'left'   => '1',
						],
					],
					'color'  => [
						'label'   => esc_html__('Border Color', 'shopengine'),
						'default' => '#f2f2f2',
					],
				],
				'selector' => self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_action_btn_sel . '):hover',
			]
		);

		$this->add_control(
			'shopengine_cl_action_btn_icon_size',
			[
				'label'      => esc_html__('Icon Size', 'shopengine'),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 18,
				],
				'selectors'  => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_action_btn_sel . ') :is(svg)' => 'width: {{SIZE}}{{UNIT}} !important;',
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_action_btn_sel . ') :is(i)::before' => 'font-size: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'shopengine_cl_action_btn_padding',
			[
				'label'      => esc_html__('Padding', 'shopengine'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px'],
				'default'    => [
					'top'      => '5',
					'right'    => '5',
					'bottom'   => '5',
					'left'     => '5',
					'unit'     => 'px',
					'isLinked' => false,
				],
				'selectors'  => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_action_btn_sel . ')' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
					'.rtl ' . self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_action_btn_sel . ')' => 'padding: {{TOP}}{{UNIT}} {{LEFT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{RIGHT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'shopengine_cl_action_btn_radius',
			[
				'label'      => esc_html__('Border Radius', 'shopengine'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'default'    => [
					'top'      => '50',
					'right'    => '50',
					'bottom'   => '50',
					'left'     => '50',
					'unit'     => '%',
					'isLinked' => true,
				],
				'selectors'  => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_action_btn_sel . ')' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'shopengine_cl_action_btn_width',
			[
				'label'      => esc_html__('Width', 'shopengine'),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 44,
				],
				'selectors'  => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_action_btn_sel . ')' => 'width: {{SIZE}}{{UNIT}} !important; box-sizing: border-box !important;',
				],
			]
		);

		$this->add_responsive_control(
			'shopengine_cl_action_btn_height',
			[
				'label'      => esc_html__('Height', 'shopengine'),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 44,
				],
				'selectors'  => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_action_btn_sel . ')' => 'height: {{SIZE}}{{UNIT}} !important; box-sizing: border-box !important;',
				],
			]
		);

		$this->add_control(
			'shopengine_cl_action_btn_gap',
			[
				'label'      => esc_html__('Gap', 'shopengine'),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 10,
				],
				'selectors'  => [
					self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart' => 'column-gap: {{SIZE}}{{UNIT}} !important; row-gap: {{SIZE}}{{UNIT}} !important; gap: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'           => 'shopengine_cl_action_btn_box_shadow',
				'fields_options' => [
					'box_shadow' => [
						'default' => [
							'horizontal' => 0,
							'vertical'   => 1,
							'blur'       => 3,
							'spread'     => 0,
							'color'      => 'rgba(0,0,0,0.06)',
						],
					],
				],
				'selector' => self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(' . $cl_action_btn_sel . ')',
			]
		);

		$this->end_controls_section(); // end of List View: Action Buttons

		/*
			=============================
			Global Font Family
			=============================
		*/
		$this->start_controls_section(
			'shopengine_global_font_family_section',
			[
				'label'     => esc_html__('Global Font Family', 'shopengine'),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'shopengine_view_mode_list_grid' => 'yes',
				],
			]
		);

		$this->add_control(
			'shopengine_global_font_family',
			[
				'label'     => esc_html__('Font Family', 'shopengine'),
				'type'      => Controls_Manager::FONT,
				'selectors' => [
					self::SELECTOR_PREFIX . 'ul.products li.product .woocommerce-loop-product__title, '
						. self::CL_SELECTOR_PREFIX . '.shopengine-cl-category, '
						. self::CL_SELECTOR_PREFIX . '.shopengine-cl-title, '
						. self::CL_SELECTOR_PREFIX . '.shopengine-cl-price .amount, '
						. self::CL_SELECTOR_PREFIX . '.shopengine-cl-price .shopengine-cl-off-tag, '
						. self::CL_SELECTOR_PREFIX . '.shopengine-cl-excerpt, '
						. self::CL_SELECTOR_PREFIX . '.shopengine-cl-rating .shopengine-product-rating-review-count, '
						. self::CL_SELECTOR_PREFIX . '.shopengine-cl-add-to-cart :is(a.add_to_cart_button, a.product_type_variable, a.product_type_grouped, a.product_type_external)' => 'font-family: "{{VALUE}}";',
				],
			]
		);

		$this->end_controls_section(); // end of Global Font Family
	}

	/**
	 * Custom List Layout ordering CSS.
	 *
	 * Emitted from this widget's own render — the same approach Archive Products already
	 * uses for its own ordering controls (generate_order_item_css()) — so that Elementor's
	 * per-widget live re-render updates the order while editing, without reloading the preview.
	 */
	private function generate_cl_order_css($settings) {

		if(($settings['shopengine_view_mode_list_grid'] ?? '') !== 'yes') {
			return;
		}

		$parent = '.shopengine-archive-products--custom-list .shopengine-cl-content';

		$content_selectors = [
			'badge'       => '.shopengine-cl-badge',
			'category'    => '.shopengine-cl-category',
			'rating'      => '.shopengine-cl-rating',
			'title'       => '.shopengine-cl-title',
			'price'       => '.shopengine-cl-price',
			'excerpt'     => '.shopengine-cl-excerpt',
			'add-to-cart' => '.shopengine-cl-add-to-cart',
		];

		$btn_selectors = [
			'add-to-cart'     => ['a.add_to_cart_button', 'a.product_type_variable', 'a.product_type_grouped', 'a.product_type_external'],
			'wishlist'        => ['.shopengine-wishlist'],
			'comparison'      => ['.shopengine-comparison'],
			'quick-view'      => ['.shopengine-quickview-trigger'],
			'direct-checkout' => ['a.shopengine_direct_checkout'],
		];

		$styles = '';

		if(!empty($settings['shopengine_cl_ordering_list'])) {

			foreach($settings['shopengine_cl_ordering_list'] as $key => $item) {

				if(!empty($content_selectors[$item['list_key']])) {

					$styles .= $parent . ' ' . $content_selectors[$item['list_key']] . '{order: ' . ($key + 1) . ';}';
				}
			}
		}

		if(!empty($settings['shopengine_cl_action_btns_ordering_list'])) {

			$btn_parent = $parent . ' .shopengine-cl-add-to-cart';

			foreach($settings['shopengine_cl_action_btns_ordering_list'] as $key => $item) {

				if(empty($btn_selectors[$item['list_key']])) {
					continue;
				}

				foreach($btn_selectors[$item['list_key']] as $btn_selector) {

					$styles .= $btn_parent . ' ' . $btn_selector . '{order: ' . ($key + 1) . ';}';
				}
			}
		}

		if($styles) {
			echo '<style>';
			shopengine_content_render($styles);
			echo '</style>';
		}
	}

	/**
	 * Custom List Layout element visibility CSS.
	 *
	 * Emitted from this widget's own render for the same reason as
	 * generate_cl_order_css() above: the show/hide settings live on this
	 * widget but the markup they target belongs to Archive Products, so a
	 * PHP-side check in that widget's render never live-updates while
	 * editing this widget's switches. CSS from here does.
	 */
	private function generate_cl_visibility_css($settings) {

		if(($settings['shopengine_view_mode_list_grid'] ?? '') !== 'yes') {
			return;
		}

		$content_parent = '.shopengine-archive-products--custom-list .shopengine-cl-content';
		$btn_parent     = $content_parent . ' .shopengine-cl-add-to-cart';

		$toggle_selectors = [
			'shopengine_cl_show_badge'           => $content_parent . ' .shopengine-cl-badge',
			'shopengine_cl_show_category'        => $content_parent . ' .shopengine-cl-category',
			'shopengine_cl_show_rating'          => $content_parent . ' .shopengine-cl-rating',
			'shopengine_cl_show_excerpt'         => $content_parent . ' .shopengine-cl-excerpt',
			'shopengine_cl_show_quickview'       => $btn_parent . ' .shopengine-quickview-trigger',
			'shopengine_cl_show_wishlist'        => $btn_parent . ' .shopengine-wishlist',
			'shopengine_cl_show_comparison'      => $btn_parent . ' .shopengine-comparison',
			'shopengine_cl_show_direct_checkout' => $btn_parent . ' a.shopengine_direct_checkout',
		];

		$styles = '';

		foreach($toggle_selectors as $setting_key => $selector) {

			// Elementor switchers store the return_value ('yes') when on and an
			// empty string when off — never 'no' — so test against 'yes'.
			if(($settings[$setting_key] ?? 'yes') !== 'yes') {

				$styles .= $selector . '{display: none !important;}';
			}
		}

		// "Max Categories": shopengine_render_list_custom_content() always renders up to
		// a fixed cap of 10 terms server-side, and the actual configured limit is enforced
		// purely here via CSS — so, like the toggles above, it can live-update in the
		// editor without a page reload.
		$cl_cats_max = isset($settings['shopengine_cl_category_max']) ? (int) $settings['shopengine_cl_category_max'] : 1;
		$cl_cats_max = max(1, min(10, $cl_cats_max));
		$styles .= $content_parent . ' .shopengine-cl-category li:nth-child(n+' . ($cl_cats_max + 1) . ') {display: none !important;}';

		if($styles) {
			echo '<style>';
			shopengine_content_render($styles);
			echo '</style>';
		}
	}

	protected function screen() {
		$settings = $this->get_settings_for_display();

		$this->generate_cl_order_css($settings);
		$this->generate_cl_visibility_css($settings);

		$tpl = Products::instance()->get_widget_template($this->get_name());

		include $tpl;
	}
}
