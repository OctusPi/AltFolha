class Mapper
{

    /** Constructor class call method createObject with array param
     * HTML tag mapper receives array of strings with tag identifier and tries to turn into a js object
     * @param {Array} params 
     */
    constructor(params){
        this.map = this.mapperObject(params);
    }
    
    /** Function create object JS DOM depending on the type tag HTML
     * 
     * @param {string} identifier 
     * @returns 
     */
    createObject(identifier)
    {
        let mode = identifier.charAt(0);
        let indx = identifier.substring(1, identifier.length);

        return {
            name  : indx,
            value : mode === '#' ? document.querySelector(identifier) : document.querySelectorAll(identifier)
        }
    }

    /** Funtion group objects JS with DOM object according to identifier HTML
     * 
     * @param {Array} params 
     * @returns 
     */
    mapperObject(params) 
    {
        let mapper_obj = {};

        if(Array.isArray(params)){
            params.forEach(e => {
                let obj = this.createObject(e);
                mapper_obj[obj.name] = obj.value;
            })
        }

        return mapper_obj;
    }
}