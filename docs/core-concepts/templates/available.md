# Available Templates

Templates define the structure and behavior of pages in Bagisto Visual storefronts.

Each template determines:

- Which sections appear on the page
- Which data is available to sections
- How customers interact with the store

Bagisto Visual uses a flexible system where templates can be built with **Blade**, **JSON**, or **YAML** formats.

Each template may automatically expose **some variables** (like `$product`, `$category`, `$order`) to the sections it loads, making it easy to create dynamic, data-driven content.

## About This Page

This page documents:

- All available default templates provided by Bagisto Visual
- The variables each template shares with its sections
- Where templates are located in the theme directory
- Example usage of shared variables in Blade sections

## cart

The **Cart Template** displays the contents of a customer's shopping cart.
It is used to show the list of products that the customer has added to their cart, along with quantities, totals, and checkout options.

### Location

```plaintext
/theme/resources/views/templates/cart.yaml
```

### Shared variables

| Variable | Type       | Description                         |
| -------- | ---------- | ----------------------------------- |
| $cart    | Cart model | The current shopping cart instance. |

### Example

Example inside a section Blade file:

```blade
@if (isset($cart))
  <h2>Cart subtotal: {{ core()->currency($cart->base_sub_total) }}</h2>
@endif
```

## category

The **Category Template** is used to display a list of products belonging to a specific category.
It typically includes features like filters, sorting options, product grids, and category banners.

### Location

```plaintext
/theme/resources/views/templates/category.json
```

### Shared variables

| Variable  | Type           | Description                           |
| --------- | -------------- | ------------------------------------- |
| $category | Category model | The currently viewed category object. |

### Example

Example inside a section Blade file:

```blade
@if (isset($category))
  <h1>{{ $category->name }}</h1>

  <p>{{ $category->description }}</p>
@endif
```

## checkout-success

The **Checkout Success Template** displays the order confirmation page after a successful checkout.
It shows the customer a summary of their completed order and any next steps or messages.

### Location

```plaintext
/theme/resources/views/templates/checkout-success.yaml
```

### Shared variables

| Variable | Type        | Description                         |
| -------- | ----------- | ----------------------------------- |
| $order   | Order model | The recently placed order instance. |

### Example

Example inside a section Blade file:

```blade
@if (isset($order))
  <h2>Thank you for your order #{{ $order->id }}!</h2>

  <p>Total: {{ core()->currency($order->base_grand_total) }}</p>
@endif
```

## checkout

The **Checkout Template** is used to display the checkout process, including customer information, shipping, and payment details.
It typically shows a summary of the cart and allows customers to complete their purchase.

### Location

```plaintext
/theme/resources/views/templates/checkout.yaml
```

### Shared variables

| Variable | Type       | Description                         |
| -------- | ---------- | ----------------------------------- |
| $cart    | Cart model | The current shopping cart instance. |

### Example

Example inside a section Blade file:

```blade
@if (isset($cart))
  <h2>Cart subtotal: {{ core()->currency($cart->base_sub_total) }}</h2>

  <p>Number of items: {{ $cart->items->count() }}</p>
@endif
```

## compare

The **Compare Template** is used to display a side-by-side comparison of selected products.
It allows customers to view product attributes and differences to help them make purchasing decisions.

### Location

```plaintext
/theme/resources/views/templates/compare.json
```

### Shared variables

| Variable              | Type  | Description                                  |
| --------------------- | ----- | -------------------------------------------- |
| $comparableAttributes | array | Attributes available for product comparison. |

### Example

Example inside a section Blade file

```blade
@if (!empty($comparableAttributes))
  <h2>Compare Products By:</h2>

  <ul>
    @foreach ($comparableAttributes as $attribute)
      <li>{{ $attribute['name'] }}</li>
    @endforeach
  </ul>
@endif
```

## error

The **Error Template** is used to display an error page when something goes wrong.
It shows an error message and error code based on what occurred (e.g., 404 not found, 500 server error).

### Location

```plaintext
/theme/resources/views/templates/error.blade.php
```

### Shared variables

| Variable   | Type    | Description          |
| ---------- | ------- | -------------------- |
| $errorCode | integer | The HTTP error code. |

### Example

```blade
@if (isset($errorCode))
  <h1>Error {{ $errorCode }}</h1>

  <p>Sorry, something went wrong.</p>
@endif
```

## index

The **Index Template** is used to display the homepage of the storefront.
It typically features banners, featured collections, featured products, and custom landing page content.

### Location

```plaintext
/theme/resources/views/templates/index.yaml
```

### Shared variables

There are **no specific shared variables** automatically passed to sections in the index template.
Sections are responsible for fetching and displaying the homepage content themselves.

### Example

Since no variable is automatically exposed, a typical section might look like:

```blade
<section>
  <h1>Welcome to our Store!</h1>
</section>
```

## page

The **Page Template** is used to render CMS pages created from the admin panel.
It displays static content like About Us, Contact, Terms, or any custom page.

### Location

```plaintext
/theme/resources/views/templates/page.yaml
```

### Shared variables

| Variable | Type         | Description                |
| -------- | ------------ | -------------------------- |
| $page    | `Page` model | The CMS page being viewed. |

### Example

Example inside a section Blade file:

```blade
@if (isset($page))
  <h1>{{ $page->title }}</h1>

  <div>{!! $page->content !!}</div>
@endif
```

## product

The **Product Template** is used to display the details of a single product.
It includes the product title, description, images, price, reviews, and add-to-cart functionality.

### Location

```plaintext
/theme/resources/views/templates/product.json
```

### Shared variables

| Variable | Type            | Description                         |
| -------- | --------------- | ----------------------------------- |
| $product | `Product` model | The product currently being viewed. |

### Example

Example inside a section Blade file:

```blade
@if (isset($product))
  <h1>{{ $product->name }}</h1>

  <p>{{ $product->short_description }}</p>

  <p>Price: {{ core()->currency($product->price) }}</p>
@endif
```

## search

The **Search Template** is used to display search results based on a customer’s query.
It renders product listings that match keywords, tags, categories, or other filters.

### Location

```plaintext
/theme/resources/views/templates/search.json
```

### Shared variables

There are **no specific shared variables** exposed automatically by the search template.
Sections should retrieve search results using request query parameters or internal APIs.

### Example

A simple section might handle search results like this:

```blade
@php
  $query = request()->get('term');
@endphp

<h2>Results for "{{ $query }}"</h2>

@livewire('search-results', ['term' => $query])
```

## auth/forgot-password

The **Forgot Password Template** displays a form that allows users to request a password reset link.
It is typically accessed from the login page if a user forgets their credentials.

### Location

```plaintext
/theme/resources/views/templates/auth/forgot-password.blade.php
```

### Shared variables

There are **no shared variables** exposed by this template.

### Example

```blade
<section>
  <h2>Forgot your password?</h2>

  <form method="POST" action="{{ route('shop.customer.forgot-password.store') }}">
    @csrf
    <input type="email" name="email" required placeholder="Your email" />
    <button type="submit">Send Reset Link</button>
  </form>
</section>
```

## auth/login

The **Login Template** displays the customer login form.
It allows users to enter their email and password to access their account.

### Location

```plaintext
/theme/resources/views/templates/auth/login.blade.php
```

### Shared variables

There are **no shared variables** exposed by this template.

### Example

Example section for the login form:

```blade
<section>
  <h2>Customer Login</h2>

  <form method="POST" action="{{ route('shop.customer.session.create') }}">
    @csrf
    <input type="email" name="email" placeholder="Email" required />
    <input type="password" name="password" placeholder="Password" required />
    <button type="submit">Login</button>
  </form>
</section>
```

## auth/register

The **Register Template** displays the customer registration form.
It allows new users to create an account by providing personal and login information.

### Location

```plaintext
/theme/resources/views/templates/auth/register.blade.php
```

### Shared variables

There are **no shared variables** exposed by this template.

### Example

Example registration form inside a section:

```blade
<section>
  <h2>Create an Account</h2>

  <form method="POST" action="{{ route('shop.customer.create') }}">
    @csrf
    <input type="text" name="first_name" placeholder="First Name" required />
    <input type="text" name="last_name" placeholder="Last Name" required />
    <input type="email" name="email" placeholder="Email" required />
    <input type="password" name="password" placeholder="Password" required />
    <input type="password" name="password_confirmation" placeholder="Confirm Password" required />
    <button type="submit">Register</button>
  </form>
</section>
```

## auth/reset-password

The **Reset Password Template** displays a form to allow users to reset their password after receiving a reset link.
This page is accessed via the password reset email sent from the Forgot Password flow.

### Location

```plaintext
/theme/resources/views/templates/auth/reset-password.blade.php
```

### Shared variables

There are **no shared variables** exposed by this template.

### Example

Example form inside a section:

```blade
<section>
  <h2>Reset Your Password</h2>

  <form method="POST" action="{{ route('shop.customer.reset-password.store') }}">
    @csrf
    <input type="hidden" name="token" value="{{ request()->route('token') }}" />

    <input type="email" name="email" placeholder="Email" required />
    <input type="password" name="password" placeholder="New Password" required />
    <input type="password" name="password_confirmation" placeholder="Confirm Password" required />

    <button type="submit">Reset Password</button>
  </form>
</section>
```

## account/add-address

The **Add Address Template** displays a form that allows customers to add a new address to their account.
It typically includes fields for name, address, city, country, and contact information.

### Location

```plaintext
/theme/resources/views/templates/account/add-address.blade.php
```

### Shared variables

There are **no shared variables** exposed by this template.

### Example

Example form inside a section:

```blade
<section>
  <h2>Add New Address</h2>

  <form method="POST" action="{{ route('shop.customer.address.create') }}">
    @csrf
    <input type="text" name="address[0]" placeholder="Address" required />
    <input type="text" name="city" placeholder="City" required />
    <input type="text" name="state" placeholder="State" required />
    <input type="text" name="postcode" placeholder="Postal Code" required />
    <input type="text" name="phone" placeholder="Phone" required />
    <button type="submit">Save Address</button>
  </form>
</section>
```

## account/addresses

The **Addresses Template** displays a list of all addresses saved by a customer in their account.
It allows customers to view, edit, or delete their saved addresses.

### Location

```plaintext
/theme/resources/views/templates/account/addresses.blade.php
```

### Shared variables

There are **no shared variables** exposed by this template.

### Example

Example section to list addresses:

```blade
<section>
  <h2>My Addresses</h2>

  @foreach (auth('customer')->user()->addresses as $address)
    <div class="address-block">
      <p>{{ $address->address1[0] }}, {{ $address->city }}</p>
      <p>{{ $address->state }}, {{ $address->country }}</p>
      <p>{{ $address->phone }}</p>

      <a href="{{ route('shop.customer.address.edit', $address->id) }}">Edit</a>
    </div>
  @endforeach
</section>

```

## account/downloadables

The **Downloadables Template** displays a list of downloadable products that a customer has purchased.
It allows customers to download digital files like e-books, software, or media after purchase.

### Location

```plaintext
/theme/resources/views/templates/account/downloadables.yaml
```

### Shared variables

There are **no shared variables** exposed by this template.

### Example

Example section for listing downloadable items

```blade
<section>
  <h2>My Downloadable Products</h2>

  @foreach (auth('customer')->user()->downloadable_products as $download)
    <div class="downloadable-item">
      <p>{{ $download->name }}</p>
      <a href="{{ route('shop.customer.downloadable.download', $download->id) }}">Download</a>
    </div>
  @endforeach
</section>
```

## account/edit-address

The **Edit Address Template** displays a form that allows customers to update an existing address saved in their account.

### Location

```plaintext
/theme/resources/views/templates/account/edit-address.yaml
```

### Shared variables

| Variable | Type            | Description                        |
| -------- | --------------- | ---------------------------------- |
| $address | `Address` model | The address instance being edited. |

### Example

Example section for editing an address:

```blade
<section>
  <h2>Edit Address</h2>

  <form method="POST" action="{{ route('shop.customer.address.update', $address->id) }}">
    @csrf
    @method('PUT')

    <input type="text" name="address1[0]" value="{{ $address->address1[0] }}" placeholder="Address" required />
    <input type="text" name="city" value="{{ $address->city }}" placeholder="City" required />
    <input type="text" name="state" value="{{ $address->state }}" placeholder="State" required />
    <input type="text" name="postcode" value="{{ $address->postcode }}" placeholder="Postal Code" required />
    <input type="text" name="phone" value="{{ $address->phone }}" placeholder="Phone" required />

    <button type="submit">Update Address</button>
  </form>
</section>
```

## account/edit-profile

The **Edit Profile Template** displays a form that allows customers to update their personal account information, such as name, gender, date of birth, and email.

### Location

```plaintext
/theme/resources/views/templates/account/edit-profile.yaml
```

### Shared variables

There are **no special shared variables** passed directly into this template.

> Sections are expected to use `auth('customer')->user()` to retrieve and update the authenticated customer's data.

### Example

Example section for editing profile:

```blade
<section>
  <h2>Edit Profile</h2>

  <form method="POST" action="{{ route('shop.customer.profile.store') }}">
    @csrf
    @method('PUT')

    <input type="text" name="first_name" value="{{ auth('customer')->user()->first_name }}" placeholder="First Name" required />
    <input type="text" name="last_name" value="{{ auth('customer')->user()->last_name }}" placeholder="Last Name" required />
    <input type="email" name="email" value="{{ auth('customer')->user()->email }}" placeholder="Email" required />

    <button type="submit">Update Profile</button>
  </form>
</section>
```

## account/order-details

The **Order Details Template** displays the complete details of a specific customer order.
It shows the order items, shipping address, billing address, totals, and current status.

### Location

```plaintext
/theme/resources/views/templates/account/order-details.yaml
```

### Shared variables

| Variable | Type          | Description                  |
| -------- | ------------- | ---------------------------- |
| $order   | `Order` model | The customer's order object. |

### Example

Example section to display order information:

```blade
<section>
  <h2>Order #{{ $order->id }}</h2>

  <p>Order Date: {{ $order->created_at->format('M d, Y') }}</p>

  <p>Total: {{ core()->currency($order->base_grand_total) }}</p>

  <h3>Items:</h3>
  <ul>
    @foreach ($order->items as $item)
      <li>{{ $item->name }} × {{ $item->qty_ordered }}</li>
    @endforeach
  </ul>
</section>
```

## accounts/orders

The **Orders Template** displays a list of all past orders placed by the customer.
Customers can view order summaries and access order details pages from here.

It typically uses a datagrid component to render the list dynamically, including pagination, filtering, and view links.

### Location

```plaintext
/theme/resources/views/templates/account/orders.yaml
```

### Shared variables

There are **no shared variables** exposed by this template.

> Customer orders are typically listed using bagisto datagrid component or retrieved inside sections manually.

### Example

Example section for embedding a datagrid component

```blade
<section>
  <h2>My Orders</h2>

  <x-shop::datagrid :src="route('shop.customers.account.orders.index')" />
</section>
```

Or a minimal manual fetch:

```blade
<section>
  <h2>My Orders</h2>

  @foreach (auth('customer')->user()->orders as $order)
    <div class="order-summary">
      <p>Order #{{ $order->id }} placed on {{ $order->created_at->format('M d, Y') }}</p>
      <p>Total: {{ core()->currency($order->base_grand_total) }}</p>

      <a href="{{ route('shop.customer.orders.view', $order->id) }}">View Details</a>
    </div>
  @endforeach
</section>
```

## account/profile

The **Profile Template** displays the customer's personal information such as name, email, and contact details.
It also typically provides options to update profile information or change the password.

### Location

```plaintext
/theme/resources/views/templates/account/profile.yaml
```

### Shared variables

There are **no shared variables** exposed by this template.

> Sections are expected to use `auth('customer')->user()` to access the currently authenticated customer's profile information.

### Example

Example section to display the customer profile:

```blade
<section>
  <h2>My Profile</h2>

  <p>Name: {{ auth('customer')->user()->first_name }} {{ auth('customer')->user()->last_name }}</p>
  <p>Email: {{ auth('customer')->user()->email }}</p>

  <a href="{{ route('shop.customer.profile.edit') }}">Edit Profile</a>
</section>
```

## account/reviews

The **Reviews Template** displays all product reviews submitted by the customer.
It shows the products reviewed, ratings given, and review comments.

### Location

```plaintext
/theme/resources/views/templates/account/reviews.yaml
```

### Shared variables

| Variable | Type                         | Description                                    |
| -------- | ---------------------------- | ---------------------------------------------- |
| $reviews | Collection of `Review` model | The list of reviews submitted by the customer. |

### Example

Example section to list customer reviews:

```blade
<section>
  <h2>My Reviews</h2>

  @foreach ($reviews as $review)
    <div class="review-item">
      <p><strong>{{ $review->product->name }}</strong></p>
      <p>Rating: {{ $review->rating }} / 5</p>
      <p>{{ $review->comment }}</p>
    </div>
  @endforeach
</section>
```

## account/wishlist

The **Wishlist Template** displays the products that a customer has added to their wishlist.
Customers can view, manage, and move wishlist items to the cart.

### Location

```plaintext
/theme/resources/views/templates/account/wishlist.yaml
```

### Shared variables

There are **no shared variables** exposed by this template.

### Example

Example section to display wishlist items:

```blade
<section>
  <h2>My Wishlist</h2>

  @foreach (auth('customer')->user()->wishlist_items as $item)
    <div class="wishlist-item">
      <p>{{ $item->product->name }}</p>
      <a href="{{ route('shop.customer.wishlist.remove', $item->id) }}">Remove</a>
    </div>
  @endforeach
</section>
```
