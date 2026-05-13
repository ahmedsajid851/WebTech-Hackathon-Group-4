<?php
class HomeController {
    public function index() {

    
        $message = "MVC is working with DB";
        require_once __DIR__.'/../model/ProductModel.php';


        $productModel = new ProductModel();
        $products = $productModel->getAllProducts();


        include __DIR__.'/../view/home.php';
    }
}
?>