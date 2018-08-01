<?php
/**
 * @var $paymentUrl
 * @var $returnUrl
 */
?>
<div>
    <a class="button alt" href="<?php echo esc_url( $paymentUrl ) ?>"><?php echo __( 'Pay', 'jwpg' ) ?></a>
    <a class="button cancel" href="<?php echo esc_url( $returnUrl ) ?>"><?php echo __( 'Return', 'jwpg' ) ?></a>
</div>
<br />
