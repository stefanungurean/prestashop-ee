{*
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 *}
{extends file='page.tpl'}

{block name='page_content_container' prepend}
    <section id="content-hook_order_confirmation" class="card">
        <div class="card-block">
            <div class="row">
                <div class="col-md-12">
                    {block name='order_confirmation_header'}
                        <h3 class="h1 card-title">
                            <i class="material-icons done">&#xE876;</i>{l s='Your order is confirmed' d='Shop.Theme.Checkout' }
                        </h3>
                    {/block}
                    <p>
                        {if $message==""}
                        {l s='An email has been sent to your mail address %email%.' d='Shop.Theme.Checkout' sprintf=['%email%' =>$email]}
                        {else}
                            {l s='%message%' d='Shop.Theme.Checkout' sprintf=['%message%' => $message]}
                        {/if}
                    </p>
                </div>
            </div>
        </div>
    </section>
{/block}

{block name='page_content_container'}
    <section id="content" class="page-content page-order-confirmation card">
        <div class="card-block">
            <div class="row">
                {block name='order_details'}
                    <div id="order-details" class="col-md-12">
                        <h3 class="h3 card-title">{l s='Order details' d='Shop.Theme.Checkout'}:{$reference}</h3>
                        <ul>
                            <li>{l s='Order reference: %reference%' d='Shop.Theme.Checkout' sprintf=['%reference%' => $reference]}</li>
                            <li>{l s='Payment method: %method%' d='Shop.Theme.Checkout' sprintf=['%method%' => $payment]}</li>
                            <li>
                                {l s='Shipping method: %method%' d='Shop.Theme.Checkout' sprintf=['%method%' => $carrier]}<br>
                                <em>{$delay}</em>
                            </li>
                        </ul>
                    </div>
                {/block}
            </div>
        </div>
    </section>
{/block}
