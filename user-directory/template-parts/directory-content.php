<?php
/**
 * Renders Directory block content
 */
?>

<div class="user-directory-content-list">
	<?php foreach ( $entries as $entry_id ) { ?>
        <div class="user-directory-content-entry" data-entry-id="<?php esc_attr_e( $entry_id ); ?>">
            <div class="directory-entry">
                <div class="directory-entry__content">
					<?php
					foreach ( $fields as $field ) {
						if ( 'image' === $field['name'] ) :
							continue;
						endif;
						$value = user_directory_get_field_value( $entry_id, $field );

						if ( isset( $value['content'] ) || isset( $value['attr'] ) ) :
							if ( $field['hidden'] ) :
								echo '<span class="d-none ' . esc_attr( $field['field_name'] ) . '" data-value="' . esc_attr( $value['attr'] ) . '"></span>';
							else :
								if ( ! empty( $value['content'] ) ) :
									$before = '';
									if ( 'user_email' === $field['name'] ) :
										$before = '<span class="sr-only">' . esc_html__( 'Email:', 'cpfcc' ) . '</span><i class="cps-icon cps-icon--mail" aria-hidden="true"></i>';
									endif;
									?>
                                    <div class="<?php echo esc_attr( $field['field_name'] ); ?>"
                                         data-value="<?php echo esc_attr( $value['attr'] ); ?>">
										<?php echo $value['content']; ?>
                                    </div>
								<?php
								endif;
							endif;
						endif;
					}
					?>
                </div>
                <div class="directory-entry__thumb">
					<?php
					$value = user_directory_get_field_value( $entry_id, $fields['image'] );
					?>
                    <div class="<?php echo esc_attr( $fields['image']['field_name'] ); ?>"
                         data-value="<?php echo esc_attr( $value['attr'] ); ?>">
						<?php echo $value['content']; ?>
                    </div>
                </div>
            </div>
        </div>
	<?php } ?>
</div>