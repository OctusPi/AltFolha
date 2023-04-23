const mapper   = new Mapper([
    '#id', 
    '#ocpiddel', 
    '#ocpform',
    '#importform',
    '#ocpdata', 
    '#ocpsearch', 
    '#ocpdelete', 
    '#msgalert', 
    '#ocpload', 
    '#pageform', 
    '#pagelist', 
    '#datapage',
    '#dataform',
    '#btnadd', 
    '#btncancel',
    '#ocpframeimage',
    '#secretaria',
    '#departamentos',
    '#departamento',
    '#listworkers',
    '.masktel', 
    '.maskcpf', 
    '.maskcnpj', 
    '.masknumb', 
    '.maskmoney',
    '.maskdata', 
    '.ocpedit',
    '.ocpcode',
    '.nomodify', 
    '.itemcheckdepartamentos',
    '.ocpinputimgform',
]);
const formproc = new Forms();
const utils    = new Utils();
const objmap   = mapper.map;


//** MANIPULE REQUESTS EVENTS */
//send form generic async
formproc.formSend({
    form   : objmap.ocpform,
    ctmsg  : objmap.msgalert,
    ctload : objmap.ocpload,
    search : objmap.ocpsearch,
    content: objmap.datapage,
    ctform : objmap.pageform,
    ctview : objmap.pagelist
});

//form import async
formproc.formSend({
    form   : objmap.importform,
    ctmsg  : objmap.msgalert,
    ctload : objmap.ocpload,
});

//form delete async
formproc.formSend({
    form   : objmap.ocpdelete,
    ctmsg  : objmap.msgalert,
    ctload : objmap.ocpload,
    search : objmap.ocpsearch,
    content: objmap.datapage
});

//form search generic
formproc.formSearch({
    form   : objmap.ocpsearch,
    ctmsg  : objmap.msgalert,
    ctload : objmap.ocpload,
    content: objmap.datapage
});

//** MANIPULE DOM ELEMENTS */
//manipule visibility components DOM form|list
utils.triggerVisibility(
    objmap.btnadd,
    objmap.pageform,
    objmap.pagelist,
    {
        form   : [objmap.ocpform, objmap.ocpdata] ,
        ctview : objmap.dataform,
        ctmsg  : objmap.msgalert,
        codes  : objmap.ocpcode,
        fields : [objmap.id]
    }
);

utils.triggerVisibility(
    objmap.btncancel,
    objmap.pagelist,
    objmap.pageform
);

utils.triggerInner({
    form  : objmap.ocpform,
    eshow : objmap.pageform,
    ehide : objmap.pagelist,
    ctload: objmap.ocpload,
    ctmsg : objmap.msgalert,
    iddel : objmap.ocpiddel,
    input : '#departamentos',

});

utils.previewImg(
    objmap.ocpinputimgform,
    objmap.ocpframeimage
);

//maskred elements form
utils.maskared(objmap.masktel, 'phone');
utils.maskared(objmap.maskcpf, 'cpf');
utils.maskared(objmap.maskcnpj, 'cnpj');
utils.maskared(objmap.masknumb, 'numb');
utils.maskared(objmap.maskmoney, 'money');
utils.maskared(objmap.maskdata, 'data');

//dinamic modeling elements
utils.nomodify(objmap.nomodify);
utils.requestHTML(objmap.secretaria, objmap.departamentos, objmap.ocpload, objmap.msgalert);
utils.requestHTML(objmap.secretaria, objmap.departamento, objmap.ocpload, objmap.msgalert);
utils.requestHTML(objmap.departamento, objmap.listworkers, objmap.ocpload, objmap.msgalert);
