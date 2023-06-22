define([
    'Magento_Customer/js/customer-data'
], function (customerData) {
    var isEmpty = function (variable) {
        if (typeof variable === 'undefined') {
            return true;
        }

        if (Array.isArray(variable) && variable.length === 0) {
            return true;
        }

        return typeof variable === 'object' && Object.keys(variable).length === 0;
    }

    var subscribeToSectionDataChanges = function (sectionName) {
        var sectionData = customerData.get(sectionName);
        sectionData.subscribe(function () {
            processCvgEventsFromSection(sectionName);
        });
    }

    var processCvgEventsFromSection = function (sectionName) {
        const sectionData = customerData.get(sectionName)();
        const cvgEvents = sectionData.cvg_events;

        if (true === isEmpty(cvgEvents)) {
            return;
        }

        for (const [eventId, eventData] of Object.entries(cvgEvents)) {
            if (eventData.triggered === true) {
                continue;
            }

            if (eventData.meta && eventData.meta.allowed_events && eventData.meta.allowed_events.length > 0) {
                for (const [, allowedEvent] of Object.entries(eventData.meta.allowed_events)) {
                    $(window).on(allowedEvent, function() {
                        window.cvg(eventData);
                    });
                }
                continue;
            }

            window.cvg(eventData);

            if (!eventData.meta || eventData.meta.cacheable !== true) {
                delete sectionData['cvg_events'][eventId];
                customerData.set(sectionName, sectionData);
            }
            eventData.triggered = true;
        }
    }

    for (const sectionName of ['cart', 'customer', 'events-section']) {
        processCvgEventsFromSection(sectionName);
        subscribeToSectionDataChanges(sectionName);
    }
});
