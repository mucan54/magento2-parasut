<?php

namespace Mnm\Prasut\Observer;

use Mnm\Model\Parasut\Client;
use Mnm\Model\Parasut\Product;
use Mnm\Model\Parasut\Invoice;
use Mnm\Model\Parasut\Account;
use Magento\Framework\Unserialize\Unserialize;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\CacheInterface as Cache;
use Magento\Store\Model\StoreManagerInterface as Store;
use Magento\Framework\App\Config\ScopeConfigInterface;


class ParasutAfterInvoice implements ObserverInterface
{

    protected $client;
    protected $product;
    protected $invoice;
    protected $account;
    protected $unserialize;
    protected $serialize;
    protected $cache;
    protected $store;
    protected $scope;

    public static $cacheId = 'Parasut';

    const CLIENT_ID = "parasut/mnm_parasut/client_id";
    const USERNAME = "parasut/mnm_parasut/username";
    const PASSWORD = "parasut/mnm_parasut/password";
    const GRANT_TYPE = "parasut/mnm_parasut/grant_type";
    const REDIRECT_URI = "parasut/mnm_parasut/redirect_uri";
    const COMPANY_ID = "parasut/mnm_parasut/company_id";

    public function __construct(ScopeConfigInterface $scope, Client $client,Product $product, Invoice $invoice, Account $account,Unserialize $unserialize, SerializerInterface $serialize, Cache $cache, Store $store)
    {
        $this->client = $client;
        $this->product = $product;
        $this->invoice = $invoice;
        $this->account = $account;
        $this->unserialize = $unserialize;
        $this->serialize = $serialize;
        $this->cache = $cache;
        $this->store = $store;
        $this->scope = $scope;
    }

    /**Observes layout_load_before event for remove templates which includes compare property.
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {


        $this->createClient();

        $_invoice = $observer->getEvent()->getInvoice();
        if ($_invoice->getUpdatedAt() == $_invoice->getCreatedAt()) {

            $this->createProducts($_invoice);
            $this->createCustomer($_invoice);

        } else {

            // Logic for when invoice is updated

        }
    }


    public function createProducts($invoice){

        $items = $invoice->getAllItems();
        foreach ($items as &$item) {
            $productArr = array(
                'data' => array (
                    'type' => 'products',
                    'attributes' => array (
                        'name' => 'xxxx xx x x x x xx '
                    )
                )
            );
            $this->client->call(Mnm\Model\Parasut\Product::class)->create($productArr);
        }

    }

    public function createCustomer($invoice){

    }


    public function createClient(){
        $data = $this->cache->load(self::$cacheId."_".$this->store->getStore()->getId());
        if (!$data) {
            $this->client = new Client([
                "client_id" => $this->scope->getValue(self::CLIENT_ID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                "username" => $this->scope->getValue(self::USERNAME, \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                "password" => $this->scope->getValue(self::PASSWORD, \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                "grant_type" => $this->scope->getValue(self::GRANT_TYPE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                "redirect_uri" => $this->scope->getValue(self::REDIRECT_URI, \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                'company_id' => $this->scope->getValue(self::COMPANY_ID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ]);

            $data = $this->serializer->serialize($this->client);
            $this->cache->save($data, self::$cacheId."_".$this->store->getStore()->getId());
        }
        else {
            $this->client = $this->serializer->unserialize($data);
        }
    }
}
