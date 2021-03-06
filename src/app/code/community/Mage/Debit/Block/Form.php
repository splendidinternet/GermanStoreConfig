<?php
/**
 * This file is part of the Mage_Debit module.
 *
 * PHP version 5
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category  Mage
 * @package   Mage_Debit
 * @author    Rouven Alexander Rieker <rouven.rieker@itabs.de>
 * @copyright 2012 ITABS GmbH / Rouven Alexander Rieker (http://www.itabs.de)
 * @copyright 2010 Phoenix Medien GmbH & Co. KG (http://www.phoenix-medien.de)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.magentocommerce.com/extension/676/
 */
/**
 * Debit Form Block
 *
 * @category  Mage
 * @package   Mage_Debit
 * @author    Rouven Alexander Rieker <rouven.rieker@itabs.de>
 * @copyright 2012 ITABS GmbH / Rouven Alexander Rieker (http://www.itabs.de)
 * @copyright 2010 Phoenix Medien GmbH & Co. KG (http://www.phoenix-medien.de)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.magentocommerce.com/extension/676/
 */
class Mage_Debit_Block_Form extends Mage_Payment_Block_Form
{
    /**
     * Construct payment form block and set template
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('debit/form.phtml');
    }

    /**
     * Returns the account bankname if applicable from the payment info instance
     *
     * @return string Bankname/Error
     */
    public function getBankName()
    {
        $blz = $this->getAccountBLZ();
        if (empty($blz)) {
            return $this->__('-- will be filled in automatically --');
        }
        $bankName = Mage::helper('debit')->getBankByBlz($blz);
        if ($bankName == null) {
            $bankName = $this->__('not available');
        }

        return $bankName;
    }

    /**
     * Returns the account blz from the payment info instance
     *
     * @return string BLZ
     */
    public function getAccountBLZ()
    {
        if ($data = $this->getInfoData('cc_type')) {
            if (!is_numeric($data)) {
                $data = Mage::helper('core')->decrypt($data);
            }

            return $data;
        } elseif ($data = $this->_getAccountData('debit_payment_acount_blz')) {
            return $data;
        } else {
            return '';
        }
    }

    /**
     * Returns the account name from the payment info instance
     *
     * @return string Name
     */
    public function getAccountName()
    {
        if ($data = $this->getInfoData('cc_owner')) {
            return $data;
        }

        return $this->_getAccountData('debit_payment_acount_name');
    }

    /**
     * Returns the account number from the payment info instance
     *
     * @return string Number
     */
    public function getAccountNumber()
    {
        $attribute = 'debit_payment_acount_number';
        if ($data = $this->getInfoData('cc_number')) {
            return $data;
        } elseif ($data = $this->_getAccountData($attribute)) {
            return $data;
        } else {
            return '';
        }
    }

    /**
     * Returns the specific value of the requested field from the
     * customer model.
     *
     * @param  string $field Attribute to get
     * @return string Data
     */
    protected function _getAccountData($field)
    {
        if (!Mage::getStoreConfigFlag('payment/debit/save_account_data')) {
            return '';
        }
        $data = $this->getCustomer()->getData($field);
        if (strlen($data) == 0) {
            return '';
        }

        return $this->htmlEscape($data);
    }

    /**
     * Returns the current customer
     *
     * @return Mage_Customer_Model_Customer Customer
     */
    public function getCustomer()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            return Mage::getSingleton('adminhtml/session_quote')->getCustomer();
        }

        return Mage::getSingleton('customer/session')->getCustomer();
    }

    /**
     * Returns the config setting if checkout is only allowed with a valid BLZ
     *
     * @return boolean true/false
     */
    public function getCheckoutValidBlz()
    {
        return Mage::getStoreConfigFlag('payment/debit/checkout_valid_blz');
    }
}
