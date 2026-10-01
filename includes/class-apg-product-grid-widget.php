<?php
/**
 * Elementor widget: WooCommerce product grid with sorting, variation
 * swatches, hover image swap, sale/sold-out badges and an Order Now button.
 *
 * @package Advanced_Product_Grid
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

class APG_Product_Grid_Widget extends Widget_Base {

	public function get_name() {
		return 'apg_product_grid';
	}

	public function get_title() {
		return __( 'Product Grid (Sort + Variations)', 'advanced-product-grid' );
	}

	public function get_icon() {
		return 'eicon-products';
	}

	public function get_categories() {
		// "general" is one of Elementor's own built-in categories, always
		// available regardless of category-registration timing — kept here
		// as a safety net alongside our own category, so the widget is
		// guaranteed to show up somewhere even if that ordering ever shifts.
		return [ 'apg-widgets', 'general' ];
	}

	public function get_keywords() {
		return [ 'woocommerce', 'product', 'grid', 'shop', 'sort', 'variation', 'woodmart', 'order now' ];
	}

	public function get_script_depends() {
		return [ 'apg-product-grid-script' ];
	}

	public function get_style_depends() {
		return [ 'apg-product-grid-style' ];
	}

	/* =========================================================
	 * CONTROLS
	 * ========================================================= */

	protected function register_controls() {
		$this->register_query_controls();
		$this->register_sorting_controls();
		$this->register_card_controls();
		$this->register_style_grid_controls();
		$this->register_style_badge_controls();
		$this->register_style_text_controls();
		$this->register_style_swatch_controls();
		$this->register_style_button_controls();
	}

	private function register_query_controls() {

		$this->start_controls_section(
			'section_query',
			[ 'label' => __( 'Query', 'advanced-product-grid' ) ]
		);

		$this->add_control(
			'products_count',
			[
				'label'   => __( 'Products Per Page', 'advanced-product-grid' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 100,
				'default' => 8,
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label'          => __( 'Columns', 'advanced-product-grid' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => '4',
				'tablet_default' => '3',
				'mobile_default' => '2',
				'options'        => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
				],
				'selectors'      => [
					'{{WRAPPER}} .apg-grid' => '--apg-cols: {{VALUE}};',
				],
			]
		);

		$cat_options = [];
		$categories  = get_terms(
			[
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
			]
		);

		if ( ! is_wp_error( $categories ) ) {
			foreach ( $categories as $cat ) {
				$cat_options[ $cat->term_id ] = $cat->name;
			}
		}

		$this->add_control(
			'categories',
			[
				'label'       => __( 'Categories', 'advanced-product-grid' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $cat_options,
				'description' => __( 'Leave empty to pull products from all categories.', 'advanced-product-grid' ),
			]
		);

		$this->add_control(
			'default_orderby',
			[
				'label'   => __( 'Default Sorting', 'advanced-product-grid' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'menu_order',
				'options' => [
					'menu_order' => __( 'Default sorting', 'advanced-product-grid' ),
					'popularity' => __( 'Popularity', 'advanced-product-grid' ),
					'rating'     => __( 'Average rating', 'advanced-product-grid' ),
					'date'       => __( 'Latest', 'advanced-product-grid' ),
					'price'      => __( 'Price: low to high', 'advanced-product-grid' ),
					'price-desc' => __( 'Price: high to low', 'advanced-product-grid' ),
				],
			]
		);

		$this->add_control(
			'on_sale_only',
			[
				'label'   => __( 'On-Sale Products Only', 'advanced-product-grid' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->end_controls_section();
	}

	private function register_sorting_controls() {

		$this->start_controls_section(
			'section_sorting_bar',
			[ 'label' => __( 'Sorting Bar', 'advanced-product-grid' ) ]
		);

		$this->add_control(
			'show_sorting',
			[
				'label'   => __( 'Show Sorting Dropdown', 'advanced-product-grid' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_result_count',
			[
				'label'   => __( 'Show Result Count', 'advanced-product-grid' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->end_controls_section();
	}

	private function register_card_controls() {

		$this->start_controls_section(
			'section_card',
			[ 'label' => __( 'Product Card', 'advanced-product-grid' ) ]
		);

		$this->add_control(
			'show_badge',
			[
				'label'   => __( 'Show Sale Badge (%)', 'advanced-product-grid' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_soldout_badge',
			[
				'label'   => __( 'Show Sold Out Badge', 'advanced-product-grid' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_swatches',
			[
				'label'   => __( 'Show Variation Swatches', 'advanced-product-grid' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'hover_swap_image',
			[
				'label'   => __( 'Swap Image on Hover (2nd Gallery Photo)', 'advanced-product-grid' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'image_ratio',
			[
				'label'   => __( 'Image Aspect Ratio', 'advanced-product-grid' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '1/1',
				'options' => [
					'1/1'   => __( 'Square (1:1)', 'advanced-product-grid' ),
					'4/5'   => __( 'Portrait (4:5)', 'advanced-product-grid' ),
					'3/4'   => __( 'Portrait (3:4)', 'advanced-product-grid' ),
					'1/1.2' => __( 'Tall (1:1.2)', 'advanced-product-grid' ),
				],
			]
		);

		$this->add_control(
			'button_text',
			[
				'label'   => __( 'Button Text', 'advanced-product-grid' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Order Now', 'advanced-product-grid' ),
			]
		);

		$this->end_controls_section();
	}

	private function register_style_grid_controls() {

		$this->start_controls_section(
			'section_style_grid',
			[
				'label' => __( 'Grid', 'advanced-product-grid' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'grid_gap',
			[
				'label'      => __( 'Grid Gap', 'advanced-product-grid' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
				'default'    => [ 'unit' => 'px', 'size' => 20 ],
				'selectors'  => [
					'{{WRAPPER}} .apg-grid' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	private function register_style_badge_controls() {

		$this->start_controls_section(
			'section_style_badges',
			[
				'label' => __( 'Badges', 'advanced-product-grid' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'badge_sale_bg',
			[
				'label'     => __( 'Sale Badge Background', 'advanced-product-grid' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#e2231a',
				'selectors' => [ '{{WRAPPER}} .apg-badge-sale' => 'background-color: {{VALUE}};' ],
			]
		);

		$this->add_control(
			'badge_sale_color',
			[
				'label'     => __( 'Sale Badge Text', 'advanced-product-grid' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [ '{{WRAPPER}} .apg-badge-sale' => 'color: {{VALUE}};' ],
			]
		);

		$this->add_control(
			'badge_soldout_bg',
			[
				'label'     => __( 'Sold Out Badge Background', 'advanced-product-grid' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [ '{{WRAPPER}} .apg-badge-soldout' => 'background-color: {{VALUE}};' ],
			]
		);

		$this->add_control(
			'badge_soldout_color',
			[
				'label'     => __( 'Sold Out Badge Text', 'advanced-product-grid' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#222222',
				'selectors' => [ '{{WRAPPER}} .apg-badge-soldout' => 'color: {{VALUE}};' ],
			]
		);

		$this->end_controls_section();
	}

	private function register_style_text_controls() {

		$this->start_controls_section(
			'section_style_text',
			[
				'label' => __( 'Title & Price', 'advanced-product-grid' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => __( 'Title Color', 'advanced-product-grid' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#333333',
				'selectors' => [ '{{WRAPPER}} .apg-title a' => 'color: {{VALUE}};' ],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .apg-title',
			]
		);

		$this->add_control(
			'price_color',
			[
				'label'     => __( 'Active Price Color', 'advanced-product-grid' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#a02128',
				'selectors' => [ '{{WRAPPER}} .apg-price ins' => 'color: {{VALUE}};' ],
			]
		);

		$this->end_controls_section();
	}

	private function register_style_swatch_controls() {

		$this->start_controls_section(
			'section_style_swatches',
			[
				'label' => __( 'Variation Swatches', 'advanced-product-grid' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'swatch_border_color',
			[
				'label'     => __( 'Border Color', 'advanced-product-grid' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#dddddd',
				'selectors' => [ '{{WRAPPER}} .apg-swatch' => 'border-color: {{VALUE}};' ],
			]
		);

		$this->add_control(
			'swatch_hover_color',
			[
				'label'     => __( 'Hover Color', 'advanced-product-grid' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#a02128',
				'selectors' => [ '{{WRAPPER}} .apg-swatch:hover' => 'border-color: {{VALUE}}; color: {{VALUE}};' ],
			]
		);

		$this->end_controls_section();
	}

	private function register_style_button_controls() {

		$this->start_controls_section(
			'section_style_button',
			[
				'label' => __( 'Order Now Button', 'advanced-product-grid' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'button_bg',
			[
				'label'     => __( 'Background', 'advanced-product-grid' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#a02128',
				'selectors' => [ '{{WRAPPER}} .apg-order-btn' => 'background-color: {{VALUE}}; border-color: {{VALUE}};' ],
			]
		);

		$this->add_control(
			'button_hover_bg',
			[
				'label'     => __( 'Hover Background', 'advanced-product-grid' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#841b21',
				'selectors' => [ '{{WRAPPER}} .apg-order-btn:hover' => 'background-color: {{VALUE}}; border-color: {{VALUE}};' ],
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'label'     => __( 'Text Color', 'advanced-product-grid' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [ '{{WRAPPER}} .apg-order-btn' => 'color: {{VALUE}};' ],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'button_typography',
				'selector' => '{{WRAPPER}} .apg-order-btn',
			]
		);

		$this->end_controls_section();
	}

	/* =========================================================
	 * RENDER
	 * ========================================================= */

	protected function render() {

		if ( ! class_exists( 'WooCommerce' ) ) {
			echo '<p>' . esc_html__( 'WooCommerce is required for this widget.', 'advanced-product-grid' ) . '</p>';
			return;
		}

		$settings = $this->get_settings_for_display();
		$paged    = max( 1, intval( get_query_var( 'paged' ) ), intval( get_query_var( 'page' ) ) );

		$orderby = $this->get_requested_orderby( $settings['default_orderby'] );
		$args    = $this->build_query_args( $settings, $orderby, $paged );
		$args    = apply_filters( 'apg_query_args', $args, $settings, $this );

		$query = new WP_Query( $args );

		echo '<div class="apg-wrapper">';

		do_action( 'apg_before_grid', $settings, $this );

		if ( 'yes' === $settings['show_sorting'] || 'yes' === $settings['show_result_count'] ) {
			echo '<div class="apg-toolbar">';

			if ( 'yes' === $settings['show_result_count'] ) {
				printf(
					'<p class="apg-result-count">%s</p>',
					esc_html(
						sprintf(
							/* translators: %d: number of products found */
							_n( '%d product found', '%d products found', $query->found_posts, 'advanced-product-grid' ),
							$query->found_posts
						)
					)
				);
			}

			if ( 'yes' === $settings['show_sorting'] ) {
				$this->render_sorting_dropdown( $orderby );
			}

			echo '</div>';
		}

		if ( $query->have_posts() ) {

			printf( '<div class="apg-grid" style="--apg-ratio:%s;">', esc_attr( $settings['image_ratio'] ) );

			while ( $query->have_posts() ) {
				$query->the_post();

				$product = wc_get_product( get_the_ID() );
				if ( ! $product ) {
					continue;
				}

				$this->render_product_card( $product, $settings );
			}

			echo '</div>';

			if ( $query->max_num_pages > 1 ) {
				$this->render_pagination( $query, $paged );
			}
		} else {
			echo '<p class="apg-no-products">' . esc_html__( 'No products found.', 'advanced-product-grid' ) . '</p>';
		}

		do_action( 'apg_after_grid', $settings, $this );

		echo '</div>';

		wp_reset_postdata();
	}

	/**
	 * Reads ?apg_orderby= from the URL if present and valid, otherwise
	 * falls back to the widget's configured default.
	 */
	private function get_requested_orderby( $default ) {

		$allowed = [ 'menu_order', 'popularity', 'rating', 'date', 'price', 'price-desc' ];

		if ( isset( $_GET['apg_orderby'] ) ) {
			$requested = wc_clean( wp_unslash( $_GET['apg_orderby'] ) );
			if ( is_string( $requested ) && in_array( $requested, $allowed, true ) ) {
				return $requested;
			}
		}

		return $default;
	}

	private function build_query_args( $settings, $orderby, $paged ) {

		$tax_query = [
			'relation' => 'AND',
			[
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => [ 'exclude-from-catalog' ],
				'operator' => 'NOT IN',
			],
		];

		if ( ! empty( $settings['categories'] ) ) {
			$tax_query[] = [
				'taxonomy' => 'product_cat',
				'field'    => 'term_id',
				'terms'    => $settings['categories'],
			];
		}

		$args = [
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'posts_per_page'      => (int) $settings['products_count'],
			'paged'               => $paged,
			'tax_query'           => $tax_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			'ignore_sticky_posts' => true,
		];

		if ( 'yes' === $settings['on_sale_only'] ) {
			$on_sale_ids      = wc_get_product_ids_on_sale();
			$args['post__in'] = ! empty( $on_sale_ids ) ? $on_sale_ids : [ 0 ];
		}

		switch ( $orderby ) {
			case 'price':
				$args['orderby']  = 'meta_value_num';
				$args['meta_key'] = '_price'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				$args['order']    = 'ASC';
				break;

			case 'price-desc':
				$args['orderby']  = 'meta_value_num';
				$args['meta_key'] = '_price'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				$args['order']    = 'DESC';
				break;

			case 'popularity':
				$args['orderby']  = 'meta_value_num';
				$args['meta_key'] = 'total_sales'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				$args['order']    = 'DESC';
				break;

			case 'rating':
				$args['orderby']  = 'meta_value_num';
				$args['meta_key'] = '_wc_average_rating'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				$args['order']    = 'DESC';
				break;

			case 'date':
				$args['orderby'] = 'date';
				$args['order']   = 'DESC';
				break;

			default: // menu_order = "Default sorting".
				$args['orderby'] = [
					'menu_order' => 'ASC',
					'title'      => 'ASC',
				];
				break;
		}

		return $args;
	}

	private function render_sorting_dropdown( $current_orderby ) {

		$options = [
			'menu_order' => __( 'Default sorting', 'advanced-product-grid' ),
			'popularity' => __( 'Sort by popularity', 'advanced-product-grid' ),
			'rating'     => __( 'Sort by average rating', 'advanced-product-grid' ),
			'date'       => __( 'Sort by latest', 'advanced-product-grid' ),
			'price'      => __( 'Sort by price: low to high', 'advanced-product-grid' ),
			'price-desc' => __( 'Sort by price: high to low', 'advanced-product-grid' ),
		];

		$preserved_args = [];
		foreach ( $_GET as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( is_array( $value ) || in_array( $key, [ 'apg_orderby', 'paged' ], true ) ) {
				continue;
			}
			$preserved_args[ sanitize_key( $key ) ] = sanitize_text_field( wp_unslash( $value ) );
		}
		?>
		<form class="apg-sorting-form" method="get">
			<?php foreach ( $preserved_args as $key => $value ) : ?>
				<input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>">
			<?php endforeach; ?>

			<select name="apg_orderby" class="apg-orderby">
				<?php foreach ( $options as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current_orderby, $value ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>

			<noscript>
				<button type="submit" class="apg-sort-submit"><?php esc_html_e( 'Sort', 'advanced-product-grid' ); ?></button>
			</noscript>
		</form>
		<?php
	}

	private function render_product_card( $product, $settings ) {

		$permalink = get_permalink( $product->get_id() );
		$sold_out  = ! $product->is_in_stock();
		$discount  = $this->get_discount_percentage( $product );

		$main_image_id  = $product->get_image_id();
		$main_image_url = $main_image_id
			? wp_get_attachment_image_url( $main_image_id, 'woocommerce_thumbnail' )
			: wc_placeholder_img_src( 'woocommerce_thumbnail' );

		$hover_image_url = '';
		if ( 'yes' === $settings['hover_swap_image'] ) {
			$gallery_ids = $product->get_gallery_image_ids();
			if ( ! empty( $gallery_ids ) ) {
				$hover_image_url = wp_get_attachment_image_url( $gallery_ids[0], 'woocommerce_thumbnail' );
			}
		}
		?>
		<div class="apg-card <?php echo $sold_out ? 'apg-sold-out' : ''; ?>">

			<div class="apg-image-wrap">

				<?php if ( $sold_out && 'yes' === $settings['show_soldout_badge'] ) : ?>
					<span class="apg-badge apg-badge-soldout"><?php esc_html_e( 'Sold Out', 'advanced-product-grid' ); ?></span>
				<?php elseif ( ! $sold_out && $discount > 0 && 'yes' === $settings['show_badge'] ) : ?>
					<span class="apg-badge apg-badge-sale">-<?php echo esc_html( $discount ); ?>%</span>
				<?php endif; ?>

				<a href="<?php echo esc_url( $permalink ); ?>" class="apg-image-link">
					<img
						src="<?php echo esc_url( $main_image_url ); ?>"
						class="apg-image apg-image-main"
						alt="<?php echo esc_attr( $product->get_name() ); ?>"
						loading="lazy"
					>
					<?php if ( $hover_image_url ) : ?>
						<img
							src="<?php echo esc_url( $hover_image_url ); ?>"
							class="apg-image apg-image-hover"
							alt="<?php echo esc_attr( $product->get_name() ); ?>"
							loading="lazy"
						>
					<?php endif; ?>
				</a>
			</div>

			<div class="apg-content">

				<?php do_action( 'apg_before_card_content', $product, $settings ); ?>

				<h3 class="apg-title">
					<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
				</h3>

				<div class="apg-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>

				<?php if ( 'yes' === $settings['show_swatches'] ) : ?>
					<?php $this->render_variation_swatches( $product, $permalink ); ?>
				<?php endif; ?>

				<a href="<?php echo esc_url( $permalink ); ?>" class="apg-order-btn">
					<?php echo esc_html( $settings['button_text'] ); ?>
				</a>

				<?php do_action( 'apg_after_card_content', $product, $settings ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Works for both simple and variable products by comparing the lowest
	 * regular price against the lowest active price (variable products
	 * compare across their variations).
	 */
	private function get_discount_percentage( $product ) {

		if ( $product->is_type( 'variable' ) ) {
			$regular = (float) $product->get_variation_regular_price( 'min', true );
			$active  = (float) $product->get_variation_price( 'min', true );
		} else {
			$regular = (float) $product->get_regular_price();
			$active  = (float) $product->get_price();
		}

		if ( $regular > 0 && $active < $regular ) {
			return (int) round( ( ( $regular - $active ) / $regular ) * 100 );
		}

		return 0;
	}

	private function render_variation_swatches( $product, $permalink ) {

		if ( ! $product->is_type( 'variable' ) ) {
			return;
		}

		$variation_attributes = $product->get_variation_attributes();
		if ( empty( $variation_attributes ) ) {
			return;
		}

		$available_variations = $product->get_available_variations();

		foreach ( $variation_attributes as $attribute_name => $options ) {

			if ( empty( $options ) ) {
				continue;
			}

			echo '<div class="apg-swatches">';

			foreach ( $options as $option ) {

				$label = $option;

				if ( taxonomy_exists( $attribute_name ) ) {
					$term = get_term_by( 'slug', $option, $attribute_name );
					if ( $term && ! is_wp_error( $term ) ) {
						$label = $term->name;
					}
				}

				$in_stock = $this->is_swatch_in_stock( $available_variations, $attribute_name, $option );

				if ( $in_stock ) {
					printf(
						'<a href="%s" class="apg-swatch">%s</a>',
						esc_url( add_query_arg( $attribute_name, $option, $permalink ) ),
						esc_html( $label )
					);
				} else {
					printf(
						'<span class="apg-swatch apg-swatch-disabled">%s</span>',
						esc_html( $label )
					);
				}
			}

			echo '</div>';
		}
	}

	/**
	 * A swatch value counts as "in stock" if at least one available
	 * variation carrying that value (or "Any") is in stock.
	 */
	private function is_swatch_in_stock( $available_variations, $attribute_name, $option ) {

		$key = 'attribute_' . $attribute_name;

		foreach ( $available_variations as $variation ) {

			if ( ! isset( $variation['attributes'][ $key ] ) ) {
				continue;
			}

			$value = $variation['attributes'][ $key ];

			if ( ( '' === $value || $value === $option ) && ! empty( $variation['is_in_stock'] ) ) {
				return true;
			}
		}

		return false;
	}

	private function render_pagination( $query, $paged ) {

		$big = 999999999;

		$links = paginate_links(
			[
				'base'      => str_replace( $big, '%#%', esc_url( add_query_arg( 'paged', $big ) ) ),
				'format'    => '?paged=%#%',
				'current'   => $paged,
				'total'     => $query->max_num_pages,
				'prev_text' => '&larr;',
				'next_text' => '&rarr;',
				'type'      => 'list',
			]
		);

		if ( $links ) {
			echo '<nav class="apg-pagination">' . wp_kses_post( $links ) . '</nav>';
		}
	}
}
