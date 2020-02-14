
let changeData = $( document.currentScript).data('data');
console.log(changeData);
var changeApp = new Vue({
    el: '#changeApp',
    data: changeData,
    methods: {
        makeChange: function () {
            let payload={};
            payload.amount=changeApp.amount;
            $.getJSON('/home/make_change',payload,function(data){
                changeData = data;
                console.log(changeData);
                 if(data.amount>0){
                     changeApp.amount=data.amount;
                 }
                 changeApp.available=data.available;
                 changeApp.change_to_give=data.change_to_give;
                 changeApp.message=data.message;
                 changeApp.status=data.status;
                 changeApp.total_dispensed=data.total_dispensed;
                 changeApp.shortage=data.shortage;
            })


        }
        ,
        refillChange: function () {
            let payload={};

            $.getJSON('/home/refill',payload,function(data){
                changeData = data;
                console.log(changeData);
                changeApp.available=data.available;
                changeApp.change_to_give=data.change_to_give;
                changeApp.message=data.message;
                changeApp.status=data.status;
                changeApp.total_dispensed=false;
                changeApp.shortage=false;
            })


        },

            formatCurrency(value) {
                let val = (value/1).toFixed(2).replace(',', '.')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            }




    },
    mounted: function () {

    }
});
