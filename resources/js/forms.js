class Forms
{   

    //constructor class inicialize method prevent submit form
    constructor(){
        this.utils  = new Utils();
        this.alerts = new Alerts();
        this.utils.offAction(window, 'submit');
    }

    /** Check that the mandatory entries have been filled in and highlight the fields not informed
     * @param {Element} form 
     * @param {String} clname 
     * @returns 
     */
    checkMandatory(form, clname = '.ocp-mandatory')
    {
        const utils  = new Utils();
        const list   = form.querySelectorAll(clname);
        const values = [];

        list.forEach(element => {
            values.push(element.value);
            element.value ? utils.rmvClass(element, 'ocp-enfase') : utils.addClass(element, 'ocp-enfase');
        });

        return values.every(value => value !== '');
    }

    /** Check if at least one input was informed
     * @param {Element} form 
     * @param {String} clname 
     * @returns 
     */
    checkLeastOne(form, clname = '.ocp-input-form')
    {
        const list   = form.querySelectorAll(clname);
        const values = [];

        list.forEach(element => {
            values.push(element.value);
        });

        const leastOne = values.filter(value => value !== '');

        return leastOne.length > 0;
    }

    /** Execute proccess request form in async backend
     * 
     * @param {Element} form 
     * @param {Object} params 
     */
    formProccess(form, params)
    {
        if(form !== null){
            form.addEventListener('submit', evt => {
                //reset last msg displayed and prevent default submit
                evt.preventDefault();
                this.alerts.resetAlert(params.ctmsg);
    
                if(params.check(form)){
                    
                    //show load async indicator
                    this.utils.asyncIndicator(params.ctload);
                
                    //prepare fetch
                    const url = form.action;
                    const opt = {
                        method:   'POST',
                        redirect: 'follow',
                        mode:     'cors',
                        cache:    'no-cache',
                        body:     new FormData(form),
                    }
    
                    //XHTR with fetch lib
                    fetch(url, opt).then(response => {
                        if (response.ok) {
                            
                            //check response redirect page follow URL
                            if(response.redirected){
                                this.alerts.showAlert(params.ctmsg, 'success');
                                window.location = response.url;
                                return;
                            }
                            
                            //Proccess JSON or HTML in function proccess of attr pramas
                            response.json().then(json => {
                                
                                //console.log(json);
                                params.proccess(json);

                            }).catch(error => {
                                
                                //register error inconsole and view alert in Page
                                console.error(error);
                                this.alerts.showAlert(params.ctmsg, 'error');

                            });
    
                        }else{
                            //case server response fail view msg erro network
                            this.alerts.showAlert(params.ctmsg, 'rededown');
                        }
                    }).catch(error => {
    
                        this.alerts.showAlert(params.ctmsg, 'warning');
                        console.error(error);
    
                    }).finally(() => {
                        this.utils.asyncIndicator(params.ctload, false);
                    });
    
                }else{
                    this.alerts.showAlert(params.ctmsg, params.txtmsg);
                }
            });
        }
    }

    /** Method exec form to send proc to backend
     * 
     * @param {Object} params 
     */
    formSend(params)
    {
        const forms = this;
        const alert = this.alerts;
        const check = this.checkMandatory;
        const feed  = this.formFeed;

        //interprete json return backend and show in alert
        const proccess = function(json){
            alert.jsonAlert(params.ctmsg, json.status);

            if(json.status.statuscode === 'success'){
                feed(params.form, json.entity);

                //reload view based in search form
                if(params.form.getAttribute('reload')){
                    forms.reloadSearch(params.search, params);
                }

                //reload view base static url
                if(params.form.getAttribute('reloadview')){
                    forms.reloadView(params.form.getAttribute('reloadview'), params);
                }
            }
            
        }

        const config = {
            check   : check,
            txtmsg  : 'mandatory',
            ctmsg   : params.ctmsg,
            ctload  : params.ctload,
            proccess: proccess
        }

        this.formProccess(params.form, config);
    }

    /** Form request info a generic search in back-end */
    formSearch(params)
    {
        const check = this.checkLeastOne;
        
        //interprete json return backend and show in alert
        const fmproc = function(json)
        {
            if(params.content !== null)
            {
                params.content.innerHTML = json.html;
            }
        }

        const config = {
            check   : check,
            txtmsg  : 'leastone',
            ctmsg   : params.ctmsg,
            ctload  : params.ctload,
            proccess: fmproc
        }

        this.formProccess(params.form, config);
    }

    /**
     * Methd feed form with jsn form backend
     * @param {Element} form 
     * @param {Object} json 
     */
    formFeed(form, json)
    {
        if(form && json)
        {
            form.reset();

            const fields = form.querySelectorAll('.ocp-input-form');
        
            fields.forEach(e => {
                if(e.type !== 'file' && json[e.id])
                {
                    e.value = json[e.id];
                } 
            });
        }
    }

    /**
     * reload html view after proccess request change in backedn
     * @param {Element} form 
     * @param {Object} params 
     */
    reloadSearch(form, params)
    {
        const forms = this;
        
        if(form)
        {
            //reset last msg displayed and show loding indicator
            this.utils.asyncIndicator(params.ctload);

            //prepare fetch
            const url = form.action;
            const opt = {
                method:   'POST',
                redirect: 'follow',
                mode:     'cors',
                cache:    'no-cache',
                body:     new FormData(form),
            }

            //XHTR with fetch lib
            fetch(url, opt).then(response => {
                if (response.ok) {
                    
                    //Proccess JSON or HTML in function proccess of attr pramas
                    response.json().then(json => {
                        //console.log(json);
                        if(params.content !== null)
                        {
                            params.content.innerHTML = json.html;
                            if(params.ctform && params.ctview){
                                forms.utils.addClass(params.ctform, 'ocp-hidden');
                                forms.utils.rmvClass(params.ctview, 'ocp-hidden');
                            }
                        }
                    }).catch(error => {
                        //register error inconsole and view alert in Page
                        console.error(error);
                        this.alerts.showAlert(params.ctmsg, 'error');
                    });
                }else{
                    //case server response fail view msg erro network
                    this.alerts.showAlert(params.ctmsg, 'rededown');
                }
            }).catch(error => {

                this.alerts.showAlert(params.ctmsg, 'warning');
                console.error(error);

            }).finally(() => {
                this.utils.asyncIndicator(params.ctload, false);
            });
        }
    }

    /**
     * reload html view after proccess request change in backedn
     * @param {Element} form 
     * @param {Object} params 
     */
     reloadView(url, params)
     {
        //reset last msg displayed and show loding indicator
        this.utils.asyncIndicator(params.ctload);
 
        //prepare fetch
        const opt = {
            method:   'GET',
            redirect: 'follow',
            mode:     'cors',
            cache:    'no-cache',
        }

        //XHTR with fetch lib
        fetch(url, opt).then(response => {
            if (response.ok) {
                
                //Proccess JSON or HTML in function proccess of attr pramas
                response.json().then(json => {
                    //console.log(json);
                    if(params.content !== null)
                    {
                        params.content.innerHTML = json.html;
                    }
                }).catch(error => {
                    //register error inconsole and view alert in Page
                    console.error(error);
                    this.alerts.showAlert(params.ctmsg, 'error');
                });
            }else{
                //case server response fail view msg erro network
                this.alerts.showAlert(params.ctmsg, 'rededown');
            }
        }).catch(error => {

            this.alerts.showAlert(params.ctmsg, 'warning');
            console.error(error);

        }).finally(() => {
            this.utils.asyncIndicator(params.ctload, false);
        });
     }
}