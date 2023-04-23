class Alerts
{
    messages = {
        error:     'Error! Não foi possível ler o JSON',
        rededown:  'Error! Verifique sua conexão!',
        warning:   'Atenção! Verifique os dados e tente novamente...',
        mandatory: 'Campos obrigatórios não informados!',
        leastone:  'Atenção! Infome pelo menos um dos campos do formulário...',
        success:   'Solicitação processada com sucesso!'
    }

    styles = {
        error:     'alert alert-danger',
        rededown:  'alert alert-danger',
        warning:   'alert alert-warning',
        notfound:  'alert alert-warning',
        mandatory: 'alert alert-warning',
        leastone:  'alert alert-info',
        duplici:   'alert alert-warning',
        success:   'alert alert-success'
    }


    /** Show Alert in element HTML of DOM
     * 
     * @param {Element} element 
     * @param {String} type
     */
    showAlert(element, type)
    {
        try{
            window.scrollTo(0, 0);
            element.innerHTML = `<div class="${this.styles[type]}" role="alert">${this.messages[type]}</div>`;
        }catch(e){
            console.log(e);
        }
        
    }

    /** Show Alert in element HTML of DOM with Json Object
     * 
     * @param {Element} element 
     * @param {Object} params 
     */
    jsonAlert(element, params)
    {
        try{
            window.scrollTo(0, 0);
            element.innerHTML = `<div class="${this.styles[params.statuscode]}" role="alert">${params.message} ${params.details}</div>`;
        }catch(e){
            console.log(e);
        }
    }

    /**Restore initial state messagem in DOM element HTML
     * 
     * @param {*} element 
     */
    resetAlert(element)
    {
        try{
            element.innerHTML = '';
        }catch(e){
            console.log(e);
        }
    }
}