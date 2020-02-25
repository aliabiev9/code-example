<?php

namespace App\Http\Controllers;

use App\Http\Resources\Shop\IndexEmptyOrderResource;
use App\Http\Resources\Shop\IndexOrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Class OrdersController
 * @package App\Http\Controllers
 */
class OrdersController extends Controller
{
    /**
     * Show User's active order
     *
     * @OA\Get(
     *     tags={"Cart"},
     *     path="/order/{discount_code}",
     *     summary="Get active order of specified user (or empty array, if there is no active Order yet)",
     *     security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="discount_code",
     *         in="path",
     *         description="discount code (optional parameter)",
     *         required=false,
     *         example="Disc_663303442",
     *         @OA\Schema(
     *         type="string",
     *         )
     *     ),
     *     @OA\Response(response="200", description="Get User's active order"),
     *     @OA\Response(response="404", description="not found")
     * )
     *
     * @return mixed
     */
    public function show()
    {
        // show current (active) order for specified user
        if ($usersOrder = Order::getFirstActiveOrder($this->auth()->user())) {
            return IndexOrderResource::make($usersOrder);
        }
        return IndexEmptyOrderResource::make($usersOrder);
    }

    /**
     * @param $discount_code
     * @return mixed
     */
    public function showWithDiscountCode($discount_code)
    {
        if ($usersOrder = Order::getFirstActiveOrder($this->auth()->user())) {
            $usersOrder->useDiscountCode($discount_code);
            return IndexOrderResource::make($usersOrder);
        }
        return IndexEmptyOrderResource::make($usersOrder);
    }


    /**
     * Add new item to (active) order
     *
     * @OA\Put (
     *     tags={"Cart"},
     *     path="/order/{product}",
     *     summary="Add new position (OrderItem) to the Order",
     *     security={{"bearerAuth":{}}},
     *       @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="Product slug",
     *         required=true,
     *         @OA\Schema(
     *         type="string",
     *         )
     *     ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="Orders Item added to the order success",
     *       ),
     *       @OA\Response(response=401, description="Unauthorized"),
     *       @OA\Response(response=404, description="Not Found"),
     * )
     *
     * @param Request $request
     * @param Product $product
     * @return array|IndexOrderResource
     */
    public function addItem(Request $request, Product $product)
    {
        /** @var User $user */
        $user = $this->auth()->user();
        $count = $request->input('count', 1);

        /** @var Order $usersOrder */
        if (!$usersOrder = Order::getFirstActiveOrder($user)) {

            $usersOrder = Order::create([
                'user_id' => $user->id,
            ]);
        }

        if ($usersOrder->addOrderItem($product, $count)) {

            return IndexOrderResource::make($usersOrder);
        }

        return [];
    }

    /**
     * Delete item from Order
     *
     * @OA\Delete (
     *     tags={"Cart"},
     *     path="/order/{product}",
     *     summary="Remove some position (OrderItem) from the Order",
     *     security={{"bearerAuth":{}}},
     *       @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="Product slug",
     *         required=true,
     *         @OA\Schema(
     *         type="string",
     *         )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Orders Item deleted from the order success",
     *       ),
     *       @OA\Response(response=401, description="Unauthorized"),
     *       @OA\Response(response=404, description="Not Found"),
     * )
     *
     *
     * @param Request $request
     * @param Product $product
     * @return array|IndexOrderResource
     * @throws \Exception
     */
    public function deleteItem(Request $request, Product $product)
    {
        /** @var User $user */
        $user = $this->auth()->user();

        /** @var Order $usersOrder */
        $usersOrder = Order::getFirstActiveOrder($user);

        if ($usersOrder && $usersOrder->deleteOrderItem($product)) {
            return IndexOrderResource::make($usersOrder);
        }

        return ['error'=>'Delete failed'];
    }

    /**
     * Increment Order Items count
     *
     * @OA\Put (
     *     tags={"Cart"},
     *     path="/order/{product}/increase",
     *     summary="Increase some position (OrderItem) count in the Order",
     *     security={{"bearerAuth":{}}},
     *       @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="Product slug",
     *         required=true,
     *         @OA\Schema(
     *         type="string",
     *         )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Orders Item count was increased in the order successfully",
     *       ),
     *       @OA\Response(response=401, description="Unauthorized"),
     *       @OA\Response(response=404, description="Not Found"),
     * )
     *
     * @param Product $product
     * @return array|IndexOrderResource
     */
    public function increaseItemCount(Product $product)
    {
        /** @var User $user */
        $user = $this->auth()->user();

        /** @var Order $usersOrder */
        $usersOrder = Order::getFirstActiveOrder($user);

        if ($usersOrder && $usersOrder->increaseOrderItemCount($product)) {
            return IndexOrderResource::make($usersOrder);
        }

        return ['error'=>'Increase failed'];
    }

    /**
     * Decrement Order Items count
     *
     * @OA\Put (
     *     tags={"Cart"},
     *     path="/order/{product}/reduce",
     *     summary="Reduce some position (OrderItem) count in the Order",
     *     security={{"bearerAuth":{}}},
     *       @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="Product slug",
     *         required=true,
     *         @OA\Schema(
     *         type="string",
     *         )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Orders Item count was reduced in the order successfully",
     *       ),
     *       @OA\Response(response=401, description="Unauthorized"),
     *       @OA\Response(response=404, description="Not Found"),
     * )
     *
     * @param Product $product
     * @return array|IndexOrderResource
     */
    public function reduceItemCount(Product $product)
    {
        /** @var User $user */
        $user = $this->auth()->user();

        /** @var Order $usersOrder */
        $usersOrder = Order::getFirstActiveOrder($user);

        if ($usersOrder && $usersOrder->reduceOrderItemCount($product)) {
            return IndexOrderResource::make($usersOrder);
        }

        return ['error'=>'Decrease failed'];
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function countItemsInCart()
    {
        if ($userOrder = Order::getFirstActiveOrder($this->auth()->user())) {
            $orderItems = OrderItem::where('order_id', $userOrder->id)->get();
            $count = 0;
            foreach ($orderItems as $item) {
                $count += $item->count;
            }
            return response()->json(['Count items in cart' => $count]);
        }
        return response()->json(['Count items in cart' => 0]);
    }
}
