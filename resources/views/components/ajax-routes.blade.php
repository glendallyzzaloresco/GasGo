<script>
// Set up AJAX routes globally
window.gasgoRoutes = {
    cartStore: '{{ route("customer.cart.store") }}',
    cartItemUpdate: '{{ route("customer.cart.item.update") }}',
    cartItemDestroy: '{{ route("customer.cart.item.destroy") }}',
    cartClear: '{{ route("customer.cart.clear") }}',
    cartSync: '{{ route("customer.cart.sync") }}',
    authenticate: '{{ route("customer.authenticate") }}',
    register: '{{ route("customer.register") }}',
    logout: '{{ route("customer.logout") }}',
    login: '{{ route("customer.login") }}',
    dashboard: '{{ route("customer.dashboard") }}',
    profileUpdate: '{{ route("customer.profile.update") }}',
    orderStore: '{{ route("customer.order.store") }}',
    orders: '{{ route("customer.orders") }}',
    checkout: '{{ route("customer.checkout") }}'
};
</script>
