<?php
/**
 * MyClass Class Doc Comment
 *
 * @category Class
 * @package  MobileMoney
 * @author   jgnacadja <unis.gnacadja@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://github.com/jgnacadja
 */
class ps_emobpaymomoModuleFrontController extends ModuleFrontController
{
    /**
     * Payement process class.
     * Send data to external  payement UI
     *
     * @return string
     **/
    public function initContent()
    {
        parent::initContent();
        
            
        $cart = $this->context->cart;
        $authorized = false;
        
        /**
         * Verify if this module is enabled and if the cart has
         * a valid customer, delivery address and invoice address
         */
        if (!$this->module->active
            || $cart->id_customer == 0
            || $cart->id_address_delivery == 0
            || $cart->id_address_invoice == 0
        ) {
            Tools::redirect('index.php?controller=order&step=1');
        }
 
        /**
         * Verify if this payment module is authorized
         */
        foreach (Module::getPaymentModules() as $module) {
            $authorized = $authorized || ($module['name'] == $this->module->name);
        }
 
        if (!$authorized) {
            die($this->l('This payment method is not available.'));
        }
 
        /** @var CustomerCore $customer */
        $customer = new Customer($cart->id_customer);
 
        /**
         * Check if this is a vlaid customer account
         */
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }
        
        /**
         * Create order in status : payment wainting
         */
        $total = (int)$cart->getOrderTotal(true, Cart::BOTH);
        
        
        
        
        $url = Context::getContext()->link->getModuleLink(
            'ps_emobpay',
            'validationapi',
            array(
                "card"=> $this->context->cart->id
            )
        );
        
        $this->context->smarty->assign(
            [
            'total' => $total,
            'validationapi' => $url]
        );

        $encrypt = trim($total);  // enCrypte money to pay
        $token= Tools::getToken(false);
        Tools::redirect(
            "https://emobpay.rintio.com/?".
            'data='.$encrypt.
            '&token='.$token.
            '&path='.urlencode($url).
            '&techno=Prestashop'
        );
    }
}
