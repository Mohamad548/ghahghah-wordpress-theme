<?php
/**
 * Inquiry form field render helpers.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render a text-like field row.
 *
 * @param array<string, mixed> $args Field args.
 */
function ghahghah_form_field( array $args ): void {
	$id          = (string) ( $args['id'] ?? '' );
	$name        = (string) ( $args['name'] ?? $id );
	$label       = (string) ( $args['label'] ?? '' );
	$required    = ! empty( $args['required'] );
	$icon        = (string) ( $args['icon'] ?? '' );
	$type        = (string) ( $args['type'] ?? 'text' );
	$placeholder = (string) ( $args['placeholder'] ?? '' );
	$span        = (string) ( $args['span'] ?? '' );
	$error_id    = $id . '-error';

	$wrap_class = 'ghahghah-form__field';
	if ( 'full' === $span ) {
		$wrap_class .= ' ghahghah-form__field--full';
	}
	?>
	<div class="<?php echo esc_attr( $wrap_class ); ?>" data-ghahghah-field="<?php echo esc_attr( $name ); ?>">
		<label class="ghahghah-form__label" for="<?php echo esc_attr( $id ); ?>">
			<?php echo esc_html( $label ); ?>
			<?php if ( $required ) : ?>
				<span class="ghahghah-form__req" aria-hidden="true">*</span>
			<?php endif; ?>
		</label>
		<div class="ghahghah-form__control">
			<?php if ( '' !== $icon ) : ?>
				<span class="ghahghah-form__icon" aria-hidden="true">
					<?php ghahghah_the_form_icon( $icon, array( 'modifiers' => array( 'muted' ) ) ); ?>
				</span>
			<?php endif; ?>
			<?php if ( 'textarea' === $type ) : ?>
				<textarea
					class="ghahghah-form__input ghahghah-form__textarea"
					id="<?php echo esc_attr( $id ); ?>"
					name="<?php echo esc_attr( $name ); ?>"
					rows="4"
					placeholder="<?php echo esc_attr( $placeholder ); ?>"
					<?php echo $required ? 'required' : ''; ?>
					aria-describedby="<?php echo esc_attr( $error_id ); ?>"
				></textarea>
			<?php else : ?>
				<input
					class="ghahghah-form__input"
					type="<?php echo esc_attr( $type ); ?>"
					id="<?php echo esc_attr( $id ); ?>"
					name="<?php echo esc_attr( $name ); ?>"
					placeholder="<?php echo esc_attr( $placeholder ); ?>"
					autocomplete="<?php echo esc_attr( (string) ( $args['autocomplete'] ?? 'on' ) ); ?>"
					<?php echo $required ? 'required' : ''; ?>
					aria-describedby="<?php echo esc_attr( $error_id ); ?>"
				/>
			<?php endif; ?>
		</div>
		<p class="ghahghah-form__error" id="<?php echo esc_attr( $error_id ); ?>" hidden data-ghahghah-error>
			<span class="ghahghah-form__error-icon" aria-hidden="true">
				<?php ghahghah_the_form_icon( 'alert-circle', array( 'modifiers' => array( 'error' ) ) ); ?>
			</span>
			<span data-ghahghah-error-text></span>
		</p>
	</div>
	<?php
}

/**
 * Render a select field.
 *
 * @param array<string, mixed> $args Field args.
 */
function ghahghah_form_select( array $args ): void {
	$id          = (string) ( $args['id'] ?? '' );
	$name        = (string) ( $args['name'] ?? $id );
	$label       = (string) ( $args['label'] ?? '' );
	$required    = ! empty( $args['required'] );
	$icon        = (string) ( $args['icon'] ?? '' );
	$placeholder = (string) ( $args['placeholder'] ?? __( 'انتخاب کنید', 'ghahghah' ) );
	$options     = is_array( $args['options'] ?? null ) ? $args['options'] : array();
	$error_id    = $id . '-error';
	$data_attrs  = is_array( $args['attrs'] ?? null ) ? $args['attrs'] : array();
	?>
	<div class="ghahghah-form__field" data-ghahghah-field="<?php echo esc_attr( $name ); ?>">
		<label class="ghahghah-form__label" for="<?php echo esc_attr( $id ); ?>">
			<?php echo esc_html( $label ); ?>
			<?php if ( $required ) : ?>
				<span class="ghahghah-form__req" aria-hidden="true">*</span>
			<?php endif; ?>
		</label>
		<div class="ghahghah-form__control ghahghah-form__control--select">
			<?php if ( '' !== $icon ) : ?>
				<span class="ghahghah-form__icon" aria-hidden="true">
					<?php ghahghah_the_form_icon( $icon, array( 'modifiers' => array( 'muted' ) ) ); ?>
				</span>
			<?php endif; ?>
			<select
				class="ghahghah-form__input ghahghah-form__select"
				id="<?php echo esc_attr( $id ); ?>"
				name="<?php echo esc_attr( $name ); ?>"
				<?php echo $required ? 'required' : ''; ?>
				aria-describedby="<?php echo esc_attr( $error_id ); ?>"
				<?php
				foreach ( $data_attrs as $attr_key => $attr_val ) {
					printf( ' %s="%s"', esc_attr( (string) $attr_key ), esc_attr( (string) $attr_val ) );
				}
				?>
			>
				<option value=""><?php echo esc_html( $placeholder ); ?></option>
				<?php foreach ( $options as $opt_value => $opt_label ) : ?>
					<?php
					if ( is_int( $opt_value ) ) {
						$opt_value = $opt_label;
					}
					?>
					<option value="<?php echo esc_attr( (string) $opt_value ); ?>"><?php echo esc_html( (string) $opt_label ); ?></option>
				<?php endforeach; ?>
			</select>
			<span class="ghahghah-form__chevron" aria-hidden="true">
				<?php ghahghah_the_form_icon( 'chevron-down', array( 'modifiers' => array( 'muted' ) ) ); ?>
			</span>
		</div>
		<p class="ghahghah-form__error" id="<?php echo esc_attr( $error_id ); ?>" hidden data-ghahghah-error>
			<span class="ghahghah-form__error-icon" aria-hidden="true">
				<?php ghahghah_the_form_icon( 'alert-circle', array( 'modifiers' => array( 'error' ) ) ); ?>
			</span>
			<span data-ghahghah-error-text></span>
		</p>
	</div>
	<?php
}
