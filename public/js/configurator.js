/**
 * Knockout view model that keeps a live price estimate for the server configurator.
 *
 * @param {number} basePrice - Base price of the selected server.
 */
function ConfiguratorViewModel(basePrice) {
    var self = this;

    self.basePrice = basePrice;
    self.selectedComponents = ko.observableArray([]);
    self.selectedByType = {};

    /**
     * Records a component choice, replacing the previous one of the same type.
     *
     * @param {string} type - Component type (e.g. RAM, CPU).
     * @param {string|number} price - Price of the chosen component.
     * @returns {void}
     */
    self.selectComponent = function(type, price) {
        // Only one component per type may be counted in the total
        if (self.selectedByType[type]) {
            self.selectedComponents.remove(self.selectedByType[type]);
        }
        self.selectedByType[type] = price;
        self.selectedComponents.push(price);
    };

    /**
     * Base price plus selected components; display-only, the server recalculates the real price.
     *
     * @returns {string} Total formatted with two decimals.
     */
    self.totalPrice = ko.computed(function() {
        var total = self.basePrice;
        self.selectedComponents().forEach(function(price) {
            total += parseFloat(price);
        });
        return total.toFixed(2);
    });
}

var vm = new ConfiguratorViewModel(parseFloat(document.getElementById('base-price').value));
ko.applyBindings(vm, document.getElementById('configurator-app'));

document.getElementById('add-to-cart')?.addEventListener('click', function() {
    const serverId = new URLSearchParams(window.location.search).get('id');
    const selectedInputs = document.querySelectorAll('input[type="radio"]:checked');

    const componentIds = Array.from(selectedInputs).map(input => parseInt(input.value));

    // Send only ids; the price is recalculated on the server. CSRF token protects the POST
    fetch(BASE_URL + '/api/cart/add.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': CSRF_TOKEN
        },
        body: JSON.stringify({
            server_id: serverId,
            component_ids: componentIds
        }) 
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadCart(); // Refresh cart contents even if it's already open
            openCart();
        }
    });
});