<?php
session_start();
include("connection/connect.php");

if(!empty($_GET["action"])) {
    $productId = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : '';
    $quantity = isset($_POST['quantity']) ? htmlspecialchars($_POST['quantity']) : '';

    switch($_GET["action"]) {
        case "add":
            if(!empty($quantity)) {
                    if(is_numeric($quantity)) {

                $stmt = $db->prepare("SELECT * FROM dishes WHERE d_id = ?");
                $stmt->bind_param('i', $productId);
                $stmt->execute();
                $productDetails = $stmt->get_result()->fetch_object();
                
                $itemArray = array(
                    $productDetails->d_id => array(
                        'title' => $productDetails->title,
                        'd_id' => $productDetails->d_id,
                        'quantity' => $quantity,
                        'price' => $productDetails->price
                    )
                );
                
                if(!empty($_SESSION["cart_item"])) {
                    if(in_array($productDetails->d_id, array_keys($_SESSION["cart_item"]))) {
                        foreach($_SESSION["cart_item"] as $k => $v) {
                            if($productDetails->d_id == $k) {
                                if(empty($_SESSION["cart_item"][$k]["quantity"])) {
                                    $_SESSION["cart_item"][$k]["quantity"] = 0;
                                }
                                $_SESSION["cart_item"][$k]["quantity"] += $quantity;
                            }
                        }
                    } else {
                        $_SESSION["cart_item"] = $_SESSION["cart_item"] + $itemArray;
                    }
                } else {
                    $_SESSION["cart_item"] = $itemArray;
                }
                $_SESSION["success_message"] = "Item successfully added to cart!";
                }
                else {
                $_SESSION["error_message"] = "Invalid quantity!";
            }
            }
            else {
        $_SESSION["error_message"] = "Quantity cannot be empty!";
    }

            break;

        case "remove":
            if(!empty($_SESSION["cart_item"])) {
                foreach($_SESSION["cart_item"] as $k => $v) {
                    if($productId == $v['d_id']) {
                        unset($_SESSION["cart_item"][$k]);
                    }
                }
                $_SESSION["success_message"] = "Item successfully removed from cart!";
            }
            break;

        case "empty":
            unset($_SESSION["cart_item"]);
            $_SESSION["success_message"] = "Cart successfully emptied!";
            break;

        case "check":
            header("location:checkout.php");
            break;
    }
}
?>
