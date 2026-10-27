# Auto Orders pre-automation snapshot

Created before automatic replenishment implementation on 2026-10-27.

The files are the pre-automation versions from Git. `AutoOrder.php` has one
existing local change in production: apply `AutoOrder.previewAutoOrders.local.patch`
after restoring it to retain the read-only `previewAutoOrders()` helper.

Restore these files if the previous manual AutoOrders flow must be reinstated.
