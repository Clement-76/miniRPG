/**
 * create a new element and return it or add it to the DOM
 * @param {string} elt        the tag name of the element
 * @param {object} properties object that contains all the properties of the new object
 * @param                     parentElt the DOM element that will be the parent of the new element
 * @throws                    if a property is invalid
 * @returns {HTMLElement}     the new element if the parameter parentElt is undefined
 */
function create(elt, properties, parentElt = false) {
    let newElt = document.createElement(elt);

    if (properties) {
        for (let property in properties) {
            // the value of the property
            let value = properties[property];

            switch (property) {
                case 'text':
                    newElt.textContent = value;
                    break;
                case 'class':
                    if (Array.isArray(value)) {
                        value.forEach((className) => {
                            newElt.classList.add(className);
                        });
                    } else {
                        newElt.classList.add(value);
                    }
                    break;
                case 'innerHTML':
                    newElt.innerHTML = value;
                    break;
                default:
                    newElt.setAttribute(property, value);
            }
        }
    }

    if (parentElt) {
        parentElt.appendChild(newElt);
    }

    return newElt;
}
