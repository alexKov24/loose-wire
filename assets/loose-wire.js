// assets/loose-wire.js
(function ($) {
    'use strict';

    class Loose {
        constructor() {
            this.ajaxurl = looseWireAjax.ajaxurl;
            this.nonce = looseWireAjax.nonce;

        }

        async pullOn(element, className, method, encodedData) {
            //const decodedData = atob(encodedData)
            const decodedData = {...JSON.parse(atob(encodedData)), ...this.getWiredValues(element)}; // Decode base64

            const res = await fetch(this.ajaxurl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'loose_wire_pull',
                    nonce: this.nonce,
                    wire_class: className,
                    wire_method: method,
                    vars: JSON.stringify(decodedData)
                })
            });

            const j = await res.json();

            if (j.success) {
                element.closest("[wire-render]").innerHTML = j.data.html;
            } else {
                console.error('Wire error:', j.data.message);
            }
        }

        getWiredValues(element){
            let o = {};
            element.closest('[wire-render]')
            .querySelectorAll('[wire-value]')
            .forEach(el => o[el.getAttribute('wire-value')] = el.value);

            return o;
        }
    }

    // Initialize when DOM is ready
    $(document).ready(function () {
        window.loose = new Loose();
    });

})(jQuery);


