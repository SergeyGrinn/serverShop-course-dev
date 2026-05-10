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