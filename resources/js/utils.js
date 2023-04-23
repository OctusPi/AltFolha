class Utils {

    /**
     * Resert form and clear sensitive inputs
     * 
     * @param {Object} params 
     */
    resetState(params) {
        if (params !== null) {

            //reset fiels forms to original value
            if(params.form !== null)
            {
                params.form.forEach(fm => {
                    if(fm !== null){
                        fm.reset();
                    }
                });
                
            }
            
            //force value predefined to mandatory fields form
            params.fields.forEach(e => {
                if (e !== null) {
                    e.id === 'id' ? e.value = 0 : e.value = '';
                }
            });

            //force value for inputs that must have unique value
            this.setcode(params.codes);

            //reset view search to empty
            if(params.ctview !== null){
                params.ctview.innerHTML = '';
            }

            //reset message
            const alerts = new Alerts();
            alerts.resetAlert(params.ctmsg);
        }
    }

    /** Change Visibility element in DOM, manipulate with css class (oc-hidden)
     * 
     * @param {Element} element 
     * @param {Boolean} status 
     */
    chnVisibility(element, status = true) {
        status ? this.rmvClass(element, 'ocp-hidden') : this.addClass(element, 'ocp-hidden');
    }

    /**
     * Change Visibility with triger element in DOM, manipulate with css class (oc-hidden)
     * @param {Element} triger 
     * @param {Element} eshow 
     * @param {Element} ehide 
     */
    triggerVisibility(triger, eshow, ehide, prmreset = null) {
        if (triger != null) {
            triger.addEventListener('click', () => {
                window.scrollTo(0, 0);
                this.rmvClass(eshow, 'ocp-hidden');
                this.addClass(ehide, 'ocp-hidden');
                this.resetState(prmreset);
            });
        }
    }

    /**
     * Trigger start proccess update or delete item in database
     * @param {Object} params 
     */
    triggerInner(params) {
        document.addEventListener('click', e => {
            let isedit   = e.target.matches('a.ocpedit');
            let isdelete = e.target.matches('a.ocpdelete');
            let ischeck  = e.target.matches('.checkdinamic');

            if (isedit) {
                params.url = e.target.getAttribute('ocpurl');
                params.execute = function (json) {
                    const forms = new Forms();
                    const utils = new Utils();

                    if (json.entity !== null) {
                        if(params.ehide && params.eshow){
                            utils.chnVisibility(params.ehide, false);
                            utils.chnVisibility(params.eshow);
                        }
                        forms.formFeed(params.form, json.entity);
                    } else {
                        const alerts = new Alerts();
                        alerts.jsonAlert(params.ctmsg, json.status);
                    }

                }

                this.asyncGet(params);
            }

            if (isdelete && params.iddel) {
                const alerts = new Alerts();
                alerts.resetAlert(params.ctmsg);
                params.iddel.value = e.target.getAttribute('deleteid');

            }

            if(ischeck){
                if(params.input === '#departamentos'){
                    let fields = document.querySelectorAll('.checkdinamic');
                    let input  = document.querySelector(params.input);
                    this.comboMult(input, fields);
                }
            }
        });
    }

    /** Checks if element of DOM has class
     * @param {Element} element 
     * @param {String} clname 
     * @returns
     */
    hasClass(element, clname) {
        return element.classList.contains(clname);
    }

    /** Add class html in element of DOM
     * @param {Element} element 
     * @param {String} clname 
     */
    addClass(element, clname) {
        if (!this.hasClass(element, clname)) {
            element.classList.add(clname);
        }
    }

    /** Remove class html in element of DOM
     * 
     * @param {Element} element 
     * @param {String} clname 
     */
    rmvClass(element, clname) {
        if (this.hasClass(element, clname)) {
            element.classList.remove(clname);
        }
    }

    /** Modify class remove and add class html
     * @param {Element} element 
     * @param {String} classin 
     * @param {String} classout 
     */
    chnClass(element, classin, classout) {
        this.rmvClass(element, classout);
        this.addClass(element, classin);
    }

    /** Disable goup elements in execution time
     * 
     * @param {Array} elements 
     */
    offElements(elements = ['button', 'input', 'select']) {

        for (const element of elements) {
            const items = document.getElementsByClassName(element);
            for (const item of items) {
                item.setAttribute('disabled', true);
            }
        }

    }

    /** Enable goup elements in execution time
     * 
     * @param {Array} elements 
     */
    onElements(elements = ['button', 'input', 'select']) {

        for (const element of elements) {
            const items = document.getElementsByClassName(element);
            for (const item of items) {
                item.removeAttribute('disabled');
            }
        }

    }

    /** Manipulate view async load indicator in DOM
     * @param {Element} indicator 
     * @param {Boolean} status 
     */
    asyncIndicator(indicator, status = true) {
        status ? this.offElements() : this.onElements();
        this.chnVisibility(indicator, status);
    }

    /**
     * Asyncrnous request backend rescue JSON object to proccess update or delete
     * @param {Object} params 
     */
    async asyncGet(params) {
        if (params.url != null) {
            this.asyncIndicator(params.ctload);

            //prepare fetch and alerts
            const alerts = new Alerts();
            const opt = {
                method: 'GET',
                redirect: 'follow',
                mode: 'cors',
                cache: 'no-cache'
            }

            await fetch(params.url, opt).then(response => {
                if (response.ok) {
                    response.json().then(json => {
                        params.execute(json);
                    }).catch(error => {
                        //register error inconsole and view alert in Page
                        alerts.showAlert(params.ctmsg, 'error');
                        console.error(error);
                    });
                } else {
                    alerts.showAlert(params.ctmsg, 'rededown');
                }
            }).catch(error => {

                alerts.showAlert(params.ctmsg, 'warning');
                console.error(error);

            }).finally(() => {
                this.asyncIndicator(params.ctload, false);
            });
        }
    }

    /** Disable default action event to element
     * 
     * @param {Element} element 
     * @param {String} action 
     */
    offAction(element, action) {
        if (element !== null) {
            element.addEventListener(action, e => {
                e.preventDefault();
            });
        }
    }

    /** Method mascared elements inputs html
     * @param {*} elements 
     * @param {*} pattner 
     */
    maskared(elements, pattner) {
        const pattners = {
            cpf  : { mask: '000.000.000-00' },
            cnpj : { mask: '00.000.000/0000-00' },
            phone: { mask: '(00) 0.0000-0000' },
            data : { mask: '00/00/0000' },

            numb : {
                mask: Number,
                min: 0,
                max: 100000000000,
            },

            money: {
                mask: Number,
                min: 0,
                max: 100000000000,
                thousandsSeparator: ".",
                scale: 2,
                padFractionalZeros: true,
                normalizeZeros: true,
                radix: ",",
                mapToRadix: ["."]
            }
        };

        if (elements != null) {
            elements.forEach(element => {
                IMask(element, pattners[pattner]);
            });
        }
    }

    /**
     * Show img preview after upload file
     * @param {Array} fields 
     * @param {Element} frame 
     */
    previewImg(fields, frame) {
        if (fields !== null && frame !== null) {
            fields.forEach(element => {
                element.addEventListener('change', e => {
                    const input = e.target;
                    if (input.files && input.files[0]) {
                        let reader = new FileReader();
                        reader.onload = function (readfile) {
                            frame.innerHTML = `<img src="${readfile.target.result}" 
                            alt="" class="ocp-picture-imgform mx-auto"/>`;
                        }
                        reader.readAsDataURL(input.files[0]);
                    }
                });
            });
        }
    }

    /**
     * Element multi check dinamic
     * @param {Element} input 
     * @param {Array} fields 
     */
    comboMult(input, fields)
    {
        if(input !== null  && fields !== null)
        {
            fields.forEach(element => {
                element.addEventListener('change', () => {
                    input.value = '';
                    fields.forEach(e => {
                        if(e.checked){
                            const concat = input.value ? ', ' : '';
                            input.value += concat+e.getAttribute('nameitem');
                        }
                    });
                });
            });
        }
    }

    requestHTML(select, content, ctload, ctmsg){
        if(select && content){
            select.addEventListener('change', e => {
                if(e.target.value)
                {
                    const input   = e.target;
                    const url     = input.getAttribute('url')+'&key='+input.value;
                    const execute = function(json){
                        content.innerHTML = json.html;
                    };
                    const params = {
                        url     : url,
                        ctload  : ctload,
                        ctmsg   : ctmsg,
                        execute : execute
                    }
                    if(input.getAttribute('url'))
                    {
                        this.asyncGet(params);
                    }
                }
            });
        }
    }

    /**
     * Generate unique code to input html
     * @param {Array} fields 
     */
    setcode(fields) {
        const letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
        const digits  = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        const size = 16;
        let code   = '';

        for (let i = 0; i < size; i++) {
            let matriz = (i > 0 && i % 2 === 0) ? digits : letters;
            let key    = Math.floor(Math.random() * matriz.length);
            code      += matriz[key];
        }

        if (fields !== null) {
            fields.forEach(element => {
                element.value = code;
            });
        }
    }

    /**
     * Bloq modyfi content field html imput
     * @param {Array} fields 
     */
    nomodify(fields){
        if(fields !== null)
        {
            fields.forEach(e => {
                e.addEventListener('keydown', ev => {
                    ev.preventDefault();
                });
                e.addEventListener('keypress', ev => {
                    ev.preventDefault();
                });
            });
        }
    }
}