<?php
/**
 * @var $paymentUrl
 * @var $returnUrl
 * @var $autoRedirect
 */
?>
<style type="text/css">
    .jibit-pay-button,
    .jibit-cancel-button {
        display: inline-block;
        margin: 0 1px;
        font-size: 14px;
        font-weight: normal;
        border-radius: 8px;
        padding: 0 32px;
        box-shadow: none;
        border: 0;
        outline: 0;
        height: 41px;
        line-height: 41px;
    }

    .jibit-pay-button {
        font-weight: bold;
        color: #fff;
        background: #0094D9;
    }

    .jibit-pay-button:hover {
        color: #fff;
        background: #0083be;
    }

    .jibit-pay-button svg {
        float: left;
        width: 19px;
        height: 19px;
        margin-top: 11px;
        margin-right: 7px;
        margin-left: -5px;
    }

    .rtl .jibit-pay-button svg {
        float: right;
        margin-left: 7px;
        margin-right: -5px;
    }

    .jibit-cancel-button {
        color: #000;
        background-color: #F0F0F0;
    }

    .jibit-cancel-button:hover {
        background-color: #e0e0e0;
    }
</style>
<div>
    <a class="jibit-pay-button" id="jibit-pay-button" href="<?php echo esc_url( $paymentUrl ) ?>">
        <svg width="21px" height="21px" viewBox="0 0 21 21" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
            <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                <g id="JibitLogo" transform="translate(0.085938, 0.175781)" fill="#FFFFFF" fill-rule="nonzero">
                    <path d="M6,19.6 C5.8,19.6 5.5,19.5 5.3,19.4 C2.2,17.6 0.3,14.5 0.2,10.9 C0.2,10.8 0.2,10.8 0.2,10.7 L0.2,1.5 C0.2,1 0.5,0.5 1,0.3 C1.5,0.1 2,0.2 2.4,0.5 L11.4,8.3 C12,8.8 12,9.6 11.5,10.2 C11,10.8 10.2,10.8 9.6,10.3 L2.8,4.4 L2.8,10.6 C2.8,10.6 2.8,10.6 2.8,10.7 C2.9,13.3 4.3,15.7 6.6,17.1 C7.2,17.5 7.4,18.3 7.1,18.9 C6.9,19.3 6.5,19.6 6,19.6 Z M10.5,20.8 C9.8,20.8 9.2,20.2 9.2,19.5 C9.2,18.8 9.8,18.2 10.5,18.2 C12.5,18.2 14.4,17.4 15.8,16 C17.2,14.6 18.1,12.7 18.1,10.8 C18.1,10.8 18.1,10.8 18.1,10.7 L18.1,1.5 C18.1,0.8 18.7,0.2 19.4,0.2 C20.1,0.2 20.7,0.8 20.7,1.5 L20.7,10.7 C20.7,10.8 20.7,10.8 20.7,10.9 C20.6,13.5 19.5,16 17.6,17.9 C15.8,19.8 13.2,20.8 10.5,20.8 Z" id="Combined-Shape"></path>
                </g>
            </g>
        </svg>
		<?php echo __( 'Pay', 'wjpg' ) ?>
    </a>
    <a class="jibit-cancel-button" href="<?php echo esc_url( $returnUrl ) ?>"><?php echo __( 'Return', 'wjpg' ) ?></a>
	<?php if ( $autoRedirect ) : ?>
        <p>
            <br />
			<?php _e( 'You will be redirected to the gateway in a while. If you didn\'t redirect automatically click on the pay button.', 'wjpg' ) ?>
        </p>
	<?php endif; ?>
</div>
<?php if ( $autoRedirect ) : ?>
    <script type="text/javascript">
      setTimeout(function () {
        window.location = '<?php echo esc_url( $paymentUrl ) ?>';
      }, 3000);
    </script>
<?php endif; ?>
<br />
