@once
@push('tollerus-scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('virtualKeyboard', {
        mountPoint: null,
        mountTerritory: null,
        mountElem: null,
        inputField: null,
        onResize: null,
        virtualKeyboardType: '',
        mount({virtualKeyboardType, neographyId = null, mountPoint, inputFieldId}) {
            // Close any other virtual keyboards that might be open
            if (this.mountElem !== null) {
                this.unmount();
            }
            // Resolve type
            switch (virtualKeyboardType) {
                case 'phonemic':
                    var template = document.getElementById('phonemic_keyboard');
                break;
                case 'native':
                    if (neographyId === null) {
                        return;
                    }
                    var template = document.getElementById('keyboard_for_'+neographyId);
                break;
                default:
                    return;
                break;
            }
            if (template === null || mountPoint === null) {
                return;
            }
            // Store values
            this.mountTerritory = mountPoint.closest('[data-keyboard-elem="territory"]');
            this.inputField = document.getElementById(inputFieldId);
            if (this.mountTerritory === null || this.inputField === null) {
                this.mountTerritory = null;
                this.inputField = null;
                return;
            }
            this.virtualKeyboardType = virtualKeyboardType;
            this.mountPoint = mountPoint;
            // Get DOM elements
            const clone = template.content.cloneNode(true);
            this.mountElem = clone.querySelector('*');
            // Mount keyboard
            this.mountPoint.appendChild(clone);
            this.calculatePosition();
            this.mountElem.focus();

            /**
             * Set event listeners
             * ===================
             */

            /**
             * If the user tabs out of the relevant area, close the keyboard.
             */
            const onFocusin = (event) => {
                if (this.mountElem === null) {
                    return;
                }
                if (event.target !== null && !(this.mountTerritory.contains(event.target) || event.target.contains(this.mountTerritory))) {
                    window.removeEventListener('focusin', onFocusin);
                    this.unmount();
                }
            };
            window.addEventListener('focusin', onFocusin);

            /**
             * If the user clicks a non-keyboard button, or clicks outside the
             * relevant area, then close the keyboard.
             */
            const onClick = (event) => {
                if (this.mountElem === null) {
                    return;
                }
                // We need to check all the buttons inside the mount territory
                let clickedNonKeyboardButton = false;
                const buttonList = this.mountTerritory.querySelectorAll('button');
                for (let i=0; buttonList!==null && i < buttonList.length; i++) {
                    if (this.mountElem.contains(buttonList[i])) {
                        // Skip actual keyboard buttons
                        continue;
                    }
                    if (event.target === buttonList[i] || buttonList[i].contains(event.target)) {
                        clickedNonKeyboardButton = true;
                    }
                }
                if (clickedNonKeyboardButton || !this.mountTerritory.contains(event.target)) {
                    window.removeEventListener('click', onClick);
                    this.unmount();
                }
            };
            window.addEventListener('click', onClick, {capture: true}); // If we let this bubble, we get redundant events due to Alpine DOM updates

            /**
             * If the user presses escape, close the keyboard.
             */
            const onKeydown = (event) => {
                if (this.mountElem === null) {
                    return;
                }
                if (event.key === 'Escape' || event.key === 'Esc') {
                    window.removeEventListener('keydown', onKeydown);
                    this.unmount();
                }
            };
            window.addEventListener('keydown', onKeydown);

            /**
             * Handle window resize events
             */
            const onResize = (event) => {this.calculatePosition();}
            this.onResize = onResize;
            window.addEventListener('resize', this.onResize);
        },
        unmount() {
            if (this.mountElem === null) {
                return;
            }
            window.dispatchEvent(new CustomEvent('close-virtual-keyboard'));
            this.mountElem.remove();
            this.mountElem = null;
            this.mountPoint = null;
            this.mountTerritory = null;
            this.inputField = null;
            window.removeEventListener('resize', this.onResize);
            this.onResize = null;
        },
        calculatePosition() {
            if (this.mountPoint === null || this.mountElem === null) {
                return;
            }
            const targetRect = this.mountPoint.getBoundingClientRect();
            this.mountElem.style.left = "-"+targetRect.x+"px";
            const tail = this.mountElem.querySelector('[data-keyboard-elem="paneltail"]');
            if (tail !== null) {
                const offset = targetRect.x + (targetRect.width/2.0);
                tail.style.left = offset.toString() + "px";
            }
        },
        click(e) {
            if (typeof e.target.dataset.glyph === "undefined") {
                var key = e.target.closest('[data-glyph]');
            } else {
                var key = e.target;
            }
            if (key === null) {
                return;
            }
            if (this.inputField !== null) {
                this.inputField.value = this.inputField.value + key.dataset.glyph;
            }
        },
    });
});
</script>
@endpush
@endonce
