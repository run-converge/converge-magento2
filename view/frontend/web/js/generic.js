define([
    'Magento_Customer/js/customer-data'
], function (customerData) {
    var subscribeToSectionDataChanges = function (sectionName) {
        var sectionData = customerData.get(sectionName);
        sectionData.subscribe(() => processSection(sectionName));
    }

    const processSection = (sectionName) => {
        const sectionData = customerData.get(sectionName)();
        processIdentify(sectionData);
        for (const eventId in sectionData.cvg_events || {}) {
            processEvent(sectionData, sectionName, eventId);
        }
    }

    const processEvent = function (sectionData, sectionName, eventId) {
        const eventData = sectionData.cvg_events?.[eventId];
        if (!eventData || eventData?.triggered) {
            return;
        }

        window.cvg(eventData);

        delete sectionData.cvg_events[eventId];
        customerData.set(sectionName, sectionData);
        eventData.triggered = true;
    }

    const processIdentify = function (sectionData) {
        const aliases = sectionData.cvg_aliases;
        const profileProperties = sectionData.cvg_profile_properties;
        if (aliases || profileProperties) {
            window.cvg({
                method: 'set',
                aliases,
                profileProperties
            })
        }
    }

    for (const sectionName of ['cart', 'customer']) {
        processSection(sectionName);
        subscribeToSectionDataChanges(sectionName);
    }
});
