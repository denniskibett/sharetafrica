# The customer app

What personal, merchant, business and developer accounts see after signing in. Staff keep the admin dashboard
(`App\Support\DashboardNav::isCustomer()` decides). Personal accounts come first; business features are on hold.

## Layout (`resources/views/layouts/app.blade.php`, customer branch)

- **No sidebar.** A top bar (`partials/customer/topbar`) on every screen: logo (home), "Hi, name", light/dark,
  notifications (latest payments, an orange dot while one is moving) and the account menu (Transactions, Sharet
  website, Profile, Account settings, Verification, Sign out). Phones show logo, "Hi, name", light/dark and the avatar.
- **Phone tab bar** (`partials/customer/tabbar`): Home, Activity, Send (centre), Receive, Account. The Account sheet
  holds the theme switch and the account menu.
- **Back on phones:** opening a panel or the Account sheet adds a history entry, so the phone's back button closes it
  instead of leaving the page (`partials/modal/slide-over`).
- **Width:** full width with the staff dashboard's padding; one column on phones.

## Theme

Colours are channels on `.sharet-app` in `resources/css/app.css`, used through Tailwind as `app-*`
(`tailwind.config.js`): `bg`, `panel`, `tile`, `raised`, `ink`, `soft`, `muted`, `faint`, `line`, `accent`, `lime`,
and the money colours `in` (green), `out` (red), `wait` (orange), `info` (blue). Light mode uses the design's paper
(`#ECECE6` / `#F4F4F0`, never plain white); dark mode near-black. The choice is the same `darkMode` setting staff use.

Fonts ship with the app (no Google Fonts request): Inter Tight for text, **Outfit for numbers** (any element with
`tabular-nums` or `app-num`, semibold; the wallet balance bold).

## Dashboard (`dashboard/customer` + `dashboard/partials/*`)

| Piece | File | Notes |
|---|---|---|
| Wallet card | `partials/wallet` | Lime card: logo, balance (eye hides it and the number, remembered per device), name, copyable @name and M-PESA number, and Send / Receive / Withdraw inside |
| This month | `partials/summary` | Money in, out, fees (with counts), today's limit, "Send again" shortcuts |
| Money in and out | `pay/partials/chart` | Per day, 7/30/90 days; the chart library loads only when scrolled into view; hidden until there is a transaction |
| Latest transactions | `pay/partials/row` | One tinted line each |
| Money panel | `partials/money` | See below |

`?panel=send|receive|withdraw` (with `&to=@name`) opens the money panel; QR codes for receiving money use this.

## Money panel (`dashboard/partials/money`)

A right-hand panel, one step at a time like M-PESA:

- **Send:** who (recent people first; Sharet @name, M-PESA number, paybill, till, bank) → how much (pay from wallet or
  M-PESA) → review (the full cost) → Sharet PIN (wallet money only) → done, following the payment live.
- **Receive:** your @name and QR code, or add money from your own M-PESA.
- **Withdraw:** to your own M-PESA or bank, from the wallet, with the PIN.
- Not verified yet: the panel says so at once, with a link to verify.

## Transactions (`pay/index`, `pay/partials/*`)

- **Rows** (`row`): icon per kind (`icon`), what it was, from → to, date, status, signed amount; the whole line tinted:
  green in, red out, orange waiting, grey and struck through when no money moved (failed, refunded, expired).
- **Receipt panel** (`detail`, in the layout so any page can open it): amount in large bold KES, from → to, the
  transaction ID with Copy, the receipt lines (recipient, account ID, bank, from, references, date, money, status),
  Download (PDF) and Share, and the progress steps with icons (`steps`).
- **Stat cards:** wallet, in, out, fees this month, each with a second line (counts, last month, waiting).
- Days and months start at midnight in Nairobi.

Data: `PayController@index` (rows + stats, JSON), `@chart`, `@show` (one payment with its receipt).
