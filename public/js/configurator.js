function ConfiguratorViewModel(basePrice) {
    var self = this;

    self.basePrice = basePrice;
    self.selectedComponents = ko.observableArray([]);
    self.selectedByType = {};

    self.selectComponent = function(type, price) {
        if (self.selectedByType[type]) {
            self.selectedComponents.remove(self.selectedByType[type]);
        }
        self.selectedByType[type] = price;
        self.selectedComponents.push(price);
    };

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

document.getElementById('add-to-cart').addEventListener('click', function() {
    const serverId = new URLSearchParams(window.location.search).get('id');
    const selectedInputs = document.querySelectorAll('input[type="radio"]:checked');

    const componentIds = Array.from(selectedInputs).map(input => parseInt(input.value));
    const totalPrice = vm.totalPrice();

    fetch('/L/course/api/cart/add.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            server_id: serverId,
            component_ids: componentIds,
            total_price: totalPrice
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