# Implementation Plan: Cart to Order Flow

This document details the complete flow from adding items to cart, applying coupons, placing an order, and automatically cleaning up the cart.

## 1. Add to Cart
**Endpoint:** `POST /api/cart`  
Authenticates the user, validates stock availability, and adds the item to the persistent cart table.

- **Request Body:**
  ```json
  {
      "product_id": 10,
      "variation_id": 5,
      "qty": 2
  }
  ```
- **Logic:**
  - Check if student exists.
  - Check if product variation exists and has sufficient stock.
  - If item exists in cart -> Update quantity.
  - If item is new -> Create cart record.

## 2. View Cart (Optional but recommended)
**Endpoint:** `GET /api/cart`  
Fetches all items currently in the authenticated student's cart.

- **Response:**
  ```json
  [
      {
          "cart_id": 1,
          "product_name": "Karate Uniform",
          "price": 1200.00,
          "qty": 2
      }
  ]
  ```

## 3. Apply Coupon
**Endpoint:** `POST /api/coupons/apply`  
Validates a coupon code for use in the order.

- **Request Body:**
  ```json
  {
      "coupon_code": "SAVE50",
      "action": "apply"
  }
  ```
- **Logic:**
  - Check if coupon code exists and `used = 0`.
  - Return coupon details (ID and Amount) for frontend to store temporarily.
  - **Important:** Does *not* mark coupon as used yet (prevents locking coupons if order is abandoned).

## 4. Place Order & Checkout
**Endpoint:** `POST /api/orders/create`  
The final step that converts cart items into orders, handles payment status, applies discounts, and cleans up.

- **Request Body:**
  ```json
  {
      "coupon_id": 123,           // Optional, from Step 3
      "payment_mode": "online",   // 'cod' or 'online'
      "rp_order_id": "order_xyz"  // Optional, if online payment
  }
  ```

- **Execution Flow (Backend Logic):**
  1. **Fetch Cart:** Retrieve all items from `cart` table for the student.
     - *Error if cart is empty.*
  2. **Calculate Totals:** Sum up (Price × Qty) for all items.
  3. **Apply Coupon:**
     - If `coupon_id` is present, fetch validate again.
     - Subtract discount from subtotal.
  4. **Create Orders:**
     - Loop through cart items.
     - Create a record in `orders` table for each item.
     - `status`: 0 (Pending) if online, 1 (Confirmed) if COD.
  5. **Update Stock:** Decrement `qty` in `variation` table.
  6. **Mark Coupon Used:** Set `used = 1` in `coupon` table.
  7. **Clear Cart:** **Delete all records** from `cart` table for this student.
  8. **Return Response:** Success message with Order IDs and Grand Total.

## 5. Verification
- **Database:**
  - `orders` table should have new records.
  - `cart` table should be empty for that student.
  - `coupon` table (if used) should show `used = 1`.
  - `variation` table should show reduced stock.

---
**Status:** ✅ Fully Implemented in `OrderApiController.php` and `CartApiController.php`.
