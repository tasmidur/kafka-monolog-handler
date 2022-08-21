# Laravel Coupons

This package can associate coupons with your Eloquent models. This might come in handy, if you need to associate voucher codes with content that is stored in your Eloquent models.

## Installation

You can install the package via composer:

```bash
composer require tasmidur/coupon
```

The package will automatically register itself.

You can publish the migration with:

```bash
php artisan vendor:publish --provider="Tasmidur\Coupon\LaravelCouponServiceProvider" --tag="coupon-migrations"
```

After the migration has been published you can create the coupons table by running the migrations:

```bash
php artisan migrate
```

You can publish the config-file with:

```bash
php artisan vendor:publish --provider=Tasmidur\Coupon\LaravelCouponServiceProvider --tag="config"
```

This is the contents of the published config file:

```php
<?php

return [

    /*
     * Table that will be used for migration
     */
    'table' => 'coupons',

    /*
     * Model to use
     */
    'model' => \Tasmidur\Coupon\Models\Coupon::class,

    /*
     * Pivot table name for coupons and other table relation
     */
    'relation_table' => 'coupon_applied',

    /*
    * Pivot table model name for coupons and other table relation
    */

    'relation_model_class' => \App\Models\Course::class,
    /*
     * List of characters that will be used for Coupons code generation.
     */
    'characters' => '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',

    /*
     * Coupons code prefix.
     *
     * Example: course2022
     * Generated Code: course2022-37JH-1PUY
     */
    'prefix' => null,

    /*
     * Coupons code suffix.
     *
     * Example: course2022
     * Generated Code: 37JH-1PUY-course2022
     */
    'suffix' => null,

    /*
     * Separator to be used between prefix, code and suffix.
     */
    'separator' => '-',

    'coupon_format'=>'*****-*****'


];
```
## Usage

The basic concept of this package is that you can create coupons, that are associated with a specific model. For example, you could have an application that sells online video courses and a voucher would give a user access to one specific video course.

## Creating coupons

### Using the facade

You can create one or multiple coupons and access by using the `coupons` facade:
* @method static array createCoupon(string $couponType, float $price, Carbon|null $expiredAt = null, int $totalAmount = 1)
* @method static mixed getCouponList(string $sortBy = "id", string $orderBy = "ASC")
* @method static mixed getCouponListWithPagination(int $length = 10, string $sortBy = "id", string $orderBy = "ASC")
* @method static bool deleteCoupon(int $id)
* @method static mixed getCoupon(int $id)
* @method static mixed updateCoupon(array $payload, int $id)
* @method static mixed check(string $code)
* @method static mixed whereApplyCoupon(string $code)
```php
//Use for Create
$coupon = Coupons::createCoupon(string $couponType, float $price, Carbon|null $expiredAt = null, int $totalAmount = 1);
//Use for get Coupon List
$coupon = Coupons::getCouponList(string $sortBy = "id", string $orderBy = "ASC");
$coupon = Coupons::getCouponListWithPagination(int $length = 10, string $sortBy = "id", string $orderBy = "ASC");
$coupon = Coupons::deleteCoupon(int $id);
$coupon = Coupons::getCoupon(int $id);
//Use for update Coupon List
$coupon = Coupons::updateCoupon(array $payload, int $id);
//Use for validity check of Coupon
$coupon = Coupons::check(string $code);
//return list of applied coupon where it applied
$coupon = Coupons::whereApplyCoupon(string $code);

```

Add the `Tasmidur\Coupon\Traits\CouponTrait` trait to your model. This way you can easily apply coupon codes and the package takes care of storing the coupon association in the database.
```php
 $course = Course::findOrFail($courseId);
 /** One Coupon Is for One Course */
 $course->applyUniqueCoupon($couponCode);
 /** all applied coupons that is associated with course */
 $coupons = Course::eloquentQuery($sortBy, $orderBy, $searchValue)->with(['category', 'coupons'])->get();
```
## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
