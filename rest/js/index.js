new Vue({
  el:'.box',
  data:{
    queryDate:{nowYear:'',nowMonth:'',nowDate:''},  // 用于查询向数据库查询记录 （年，月，日）
    currentDate:{nowYear:'',nowMonth:'',nowDate:''}, // 用于恢复今天
    totalDate:[],   // 存放日历数据
    motionItem:[],  // 休息item
    allPersonnel:[],  // 人员
    record:[],  // 当前表记录
    queryTime:{startTime:'',endTime:'',state:''},  // 查询的时间段
    tips:false, // 有无记录状态
    personnelSet:[],
    itemSet:[],
    name:'',
    spare:[]
  },
  methods:{
    // 登记
    sign(row,list){
      // 签到人员,项目
      let item = this.motionItem[list].item;
      let personnel = this.allPersonnel[row];
      let _this = this;
      // 是否是本人操作
      if(this.name == personnel){
        // 查找项目是否已经被人员重复签到
        for(let i in _this.record){
          if(_this.record[i][personnel]){
            let existence = _this.record[i][personnel];
            if(existence.indexOf(item) == -1){
              _this.record[i][personnel].push(item);
              // 只有数据被更改到才会发送请求
              // 把打卡记录转成字符串导入数据库
              let status = JSON.stringify(_this.record);
              let startDate = `${this.queryDate.nowYear}-${this.queryDate.nowMonth}-${this.queryDate.nowDate} ${this.queryTime.startTime}`;
              let endDate = `${this.queryDate.nowYear}-${this.queryDate.nowMonth}-${this.queryDate.nowDate} ${this.queryTime.endTime}`;
              axios.post('./register.php',`status=${status}&register=register&startDate=${startDate}&endDate=${endDate}`).then(function(res){
                if(res.data.state == '200'){
                  _this.record = JSON.parse(res.data.record[0]['status']);
                }else if(res.data.state == '402'){
                  _this.record = JSON.parse(res.data.record[0]['status']);
                  alert('已过打卡有效期');
                }else if(res.data.state == '401'){
                  alert('参数有误');
                }else{
                  alert(res.data);
                }
              })
            }
          }
        }
      }else{
        alert('不能替他人操作');
      } 
    },
    // 上个月
    lastMonth:function(){
      this.queryDate.nowMonth -= 1;
      this.queryDate.nowMonth < 1 ? (this.queryDate.nowMonth = 12,this.queryDate.nowYear--) : this.queryDate.nowMonth;
      this.totalDate = [];
      this.days();
      this.getTable();      
    },
    // 下个月
    nextMonth:function(){
      this.queryDate.nowMonth += 1;
      this.queryDate.nowMonth > 12 ? (this.queryDate.nowMonth = 1,this.queryDate.nowYear++) : this.queryDate.nowMonth;
      this.totalDate = [];
      this.days();
      this.getTable();      
    },
    // 获取每个月的所有天数数据
    days:function(){
      let _thismonth = 42 ; 
      // 获取某月份第一天周几
      let firstDay = new Date(this.queryDate.nowYear,this.queryDate.nowMonth-1,1).getDay();
      firstDay == 0 ? firstDay = 7 : firstDay;
      // 获取上个月天数
      let lastMonth = new Date(this.queryDate.nowYear,this.queryDate.nowMonth-1,0).getDate();
      // 获取某月总天数
      let dayNumber = new Date(this.queryDate.nowYear,this.queryDate.nowMonth,0).getDate();
      
      for(let i=1;i<=_thismonth;i++){
        // 填补上个月剩余天数数据
        let day = lastMonth - firstDay + i;
        let dayFlag = false;
        // 填补当前月天数数据
        if(i > firstDay){
          day = i - firstDay;
          dayFlag = true;
          // 填补下个月剩余天数数据
          if(i > dayNumber + firstDay){
            day = i - dayNumber - firstDay;
            dayFlag = false;
          }
        }
        this.totalDate.push({
          num: day,
          now: dayFlag
        })
      }
    },
    // 点击日期高亮、查询往日打卡记录
    pastDay(multiple,number){
      let index = (multiple - 1) * 7 + number-1;
      let now = this.totalDate[index].now;
      let num = this.totalDate[index].num;
      if(now){
        this.queryDate.nowDate = num;
        this.getTable();
      }
    },
    // 上午
    morning(){
      this.queryTime = {startTime:'00:00:00',endTime:'15:00:00',state:'morning'};
      this.getTable();
    },
    // 下午
    afternoon(){
      this.queryTime = {startTime:'15:00:00',endTime:'23:59:59',state:'afternoon'};
      this.getTable();      
    },
    // 恢复今天
    todays(){
      // 防止多次没有必要的
      if(JSON.stringify(this.queryDate) != JSON.stringify(this.currentDate)){
        // 由于会把原对象中的值污染，解决方法转字符串再转js对象
        this.queryDate = JSON.parse(JSON.stringify(this.currentDate));
        this.getTable();
        this.totalDate = [];
        this.days();
      }
    },
    // 获取运动项目
    getTable(){
      let str;
      let _this = this;
      let url = window.location.search;
      url.indexOf('?') != -1 ? str = url.split("=")[1] : str ;
      axios.post('./getdata.php',`str=${str}`).then(res =>{
        if(res.data.state == '200'){
          _this.motionItem = res.data.item;
          // 获取用户名
          res.data.info != 'null' ? _this.name = res.data.info[1] : _this.name = res.data.info;
          // 创建空表
          _this.record = [];
          _this.spare = _this.allPersonnel;
          _this.allPersonnel = [];
          for(let i=0;i<res.data.member.length;i++){
            let member = res.data.member[i].personnel;
            _this.record.push({[member]:[]});
          }
          let status = JSON.stringify(_this.record);
          let startDate = `${this.queryDate.nowYear}-${this.queryDate.nowMonth}-${this.queryDate.nowDate} ${this.queryTime.startTime}`;
          let endDate = `${this.queryDate.nowYear}-${this.queryDate.nowMonth}-${this.queryDate.nowDate} ${this.queryTime.endTime}`;
          axios.post('./register.php',`status=${status}&startDate=${startDate}&endDate=${endDate}`).then(res => {
            if(res.data.state == '200'){
              let len = res.data.record.length;
              if(len > 0){
                _this.record = JSON.parse(res.data.record[0]['status']);
                for(let i=0;i<_this.record.length;i++){
                  for(let key in _this.record[i]){
                    _this.allPersonnel.push(key);
                  }
                }
                _this.tips = false;
              }else{
                _this.allPersonnel = _this.spare;
                _this.tips = true;
              }
            }
          })
        }
      })
    }
  },
  mounted(){
    // 进入页面获取当前日期,记录当天信息
    let date = new Date();
    this.queryDate.nowYear = date.getFullYear();
    this.queryDate.nowMonth = date.getMonth()+1;
    this.queryDate.nowDate = date.getDate();

    this.currentDate.nowYear = date.getFullYear();
    this.currentDate.nowMonth = date.getMonth()+1;
    this.currentDate.nowDate = date.getDate();
    
    // 获取当前时间段
    date.getHours() < 15 ? (this.queryTime.startTime = '00:00:00',this.queryTime.endTime = '15:00:00',this.queryTime.state = 'morning') : (this.queryTime.startTime = '15:00:00',this.queryTime.endTime = '23:59:59',this.queryTime.state = 'afternoon');
    this.days();

    this.getTable();
  }
})