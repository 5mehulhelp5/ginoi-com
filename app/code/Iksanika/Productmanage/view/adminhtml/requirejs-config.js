/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            productmanageGrid:      "Iksanika_Productmanage/catalog/grid",
            'Magento_Ui/templates/grid/cells/thumbnail/preview.html':
            'Iksanika_Productmanage/templates/grid/cells/thumbnail/preview.html',
        },
        '.admin__control-thumbnail': {
            apmImage:               "Iksanika_Productmanage/js/apmImage"
        }
    },
    paths: {
        'apmImage': "Iksanika_Productmanage/js/apmImage"
    },
    shim: {
        'apmImage': {
            deps: ['jquery']
        }
    },
    deps: [
        'Magento_Catalog/catalog/product'
    ]
};