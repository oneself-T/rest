new Vue({
    el:'.box',
    data:{
        item:[],
        member:[],
        itemName:'',
        personnelName:''
    },
    methods:{
        // 删除人员
        deletePersonnel(id){
            this.del('deletePersonnel',id);
        },
        // 删除项目
        deleteItem(id){
            this.del('deleteItem',id);
        },
        // 添加人员
        addPersonnel(){
            let name = this.personnelName;
            this.add('addPersonnel',name);
        },
        // 添加项目
        addItem(){
            let name = this.itemName;
            this.add('addItem',name);
        },
        add(classify,value){
            if(value != '' && value.indexOf(' ') == -1){
                let _this = this;
                axios.post('./manage.php',`${classify}=${value}`).then(res => {
                    if(res.data.state == '200'){
                        alert('添加成功');
                        if(res.data.member){
                            _this.member = res.data.member;
                        }
                        if(res.data.item){
                            _this.item = res.data.item;
                        }
                        _this.itemName = '';
                        _this.personnelName = '';
                    }
                })
            }
        },
        del(classify,id){
            let _this = this;
            axios.post('./manage.php',`${classify}=${classify}&id=${id}`).then(res => {
                if(res.data.state == '200'){
                    alert('删除成功');
                    if(res.data.member){
                        _this.member = res.data.member;
                    }
                    if(res.data.item){
                        _this.item = res.data.item;
                    }
                }
            }) 
        }
    },
    mounted(){
        let _this = this;
        axios.get('./manage.php').then(res => {
            if(res.data.state == '200'){
                _this.item = res.data.item;
                _this.member = res.data.member;
            }
        })
    }
})