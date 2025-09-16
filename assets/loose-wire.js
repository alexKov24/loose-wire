// assets/loose-wire.js
(function ($) {
    'use strict';

    class Loose {
        constructor() {
            this.ajaxurl = looseWireAjax.ajaxurl;
            this.nonce = looseWireAjax.nonce;

        }

        setup() {

            document.addEventListener("click", (e) => {

                const closest = e.target.closest('[wire\\:click]')

                if (closest && closest.getAttribute('wire:click')) {

                    const method = closest.getAttribute('wire:click')
                    const wiredData = this.getWiredData(e.target);
                    const wiredParent = this.getWiredParent(e.target);
                    const className = wiredData?.common?.className;

                    if (className && method) {
                        this.pullOn(wiredParent, className, method);
                    }

                }
            })
        }


        async pullOn(element, className, method) {

            const res = await fetch(this.ajaxurl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'loose_wire_pull',
                    nonce: this.nonce,
                    wire_class: className,
                    wire_method: method,
                    vars: JSON.stringify(this.gatherDomData(element))
                })
            });

            const j = await res.json();

            if (j.success) {
                element.closest("[wire\\:data]").outerHTML = j.data.html;
            } else {
                console.error('Wire error:', j.data.message);
            }
        }

        getWiredParent(el) {
            return el.closest('[wire\\:data]');
        }

        /**
         * A wired object comes with a set of predefined variables set in wire:data property
         * It also comes with a set of instructions to gather new data
         * This new data is nested in inputs, selects and other element - called `nestedData`
         * @returns All data combined together
         */
        gatherDomData(element) {
            const wireData = this.getWiredData(element).props;
            const nestedData = this.getNestedValues(element);

            console.log(wireData, nestedData);
            return { ...wireData, ...nestedData }; // override stale data
        }

        /**
         * If element has a parent returns its data, null otherwise
         * @param {Element} el 
         */
        getWiredData(el) {
            const wiredParent = el.closest('[wire\\:data]');
            if (!wiredParent) {
                console.error("Wire parent not defined for ", el);
                return null;
            }
            const attr = wiredParent.getAttribute('wire:data');
            return JSON.parse(attr);
        }

        /**
         * For an input like
         * `<input wire-value='count' value='1'>`
         * would return {'count':'1'}
         * @returns {Object} containing all nested property-value pairs
         */
        getNestedValues(element) {
            let o = {};

            element.closest('[wire\\:data]')
                .querySelectorAll('[wire-value]')
                .forEach(el => o[el.getAttribute('wire-value')] = el.value);

            return o;
        }
    }

    // Initialize when DOM is ready
    $(document).ready(function () {
        window.loose = new Loose();
        window.loose.setup();
    });

})(jQuery);


