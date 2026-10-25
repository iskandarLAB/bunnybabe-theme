# Bunny Babe theme — setup guide

## 1. Before you upload
- WordPress installed on bunnybabe.my with SSL (https) on.
- Plugins installed and active: **WooCommerce**, **CHIP for WooCommerce**, **EasyParcel**.
- Settings → Permalinks → **Post name**.

## 2. Upload the theme
Appearance → Themes → Add New → Upload Theme → choose `bunnybabe.zip` → Install → **Activate**.

## 3. Run setup (one click)
Appearance → **Bunny Babe Setup** → **Run setup**. It creates only what is missing:
- 5 products (Vanilla, Secret, Aurelle, Marsh, Swift) at RM39 with photos, in the "Bunny Babe" category
- Voucher **BUNNY5** (RM5, first order only)
- "Home" page as the front page + "Track Order" page
- Classic WooCommerce checkout (old content is kept as a backup)
- Store set to Malaysia only, currency RM
- Shipping zone "Malaysia" with RM5 flat rate

## 4. Your settings
- Appearance → Customize → **Bunny Babe**: WhatsApp number, affiliate form link, courier tracking link (use `{tracking}` where the AWB goes), top bar text.
- WooCommerce → Settings → Payments → **CHIP**: turn on and enter your keys there.

## How the store rules work
| Rule | Where |
|---|---|
| 1 = RM39, 2 = RM59, 5 = RM99 (mixed scents, best combo) | Automatic "Bundle savings" line at checkout |
| Shipping RM5, FREE from 2 bottles | Flat rate in the Malaysia zone; the theme makes it free at 2+ |
| BUNNY5 first order only | Checked by phone + email at checkout |
| RM5 voucher for the 2nd order | Created automatically when a first order is paid; shown on the thank-you page, in the order email and as an order note |
| Claim-voucher WhatsApp numbers | WooCommerce → **Voucher leads** (with an "Open WhatsApp" link) |

Change any price or rule in `inc/config.php`.

## Shipping + tracking (EasyParcel)
1. Book the parcel in EasyParcel as usual.
2. In the order, fill the **Bunny Babe tracking** box (courier + tracking number).
3. Change the order status: **Processing** → **Shipped** → **Out for delivery** → **Completed**. Bulk actions work too.
4. Customers see this on the Track Order page using their order number + phone.

## Test before going live
Place a real order end to end (small amount), check the thank-you page, the email, then Track Order.
