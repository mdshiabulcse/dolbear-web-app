<template lang="">
    <div class="container ">
        <div class="card_new ">
        <h2 class="card-title_new">RECOMMENDED BY</h2>
        <div class="row">
            <div class="col-12 col-md-6 d-flex justify-content-center mb-3 mb-md-0">
                <img :src="details.image" class="recomended-section-image" alt="" >
                <div class="card-details d-flex flex-column justify-content-center ms-3">
                  <h4>{{ details.name }}</h4>
                  <p class="comment">{{ details.description }}</p>
                
                </div>
            </div>
            <div class="col-md-6 d-flex justify-content-center">

                <div class="recomeded-slider">

                    <div class="recomended-item" v-for="(item, index) in recommends" :key="index">
                            <img class="recomended-section-image" @click="onShowDetails(item)" :src="item.image" alt="">
                        <div class="item-detail">
                            <h4>{{ item.name }}</h4>
                            <p>{{ item.description.slice(0, 40) }}{{ item.description.length > 20 ? '...' : '' }}</p>
                        </div>
                    </div>
                  
                </div>
                
            </div>
        </div>
        </div>

    </div>
</template>
<script>
export default {
    name: 'recommended_by',

  data() {
    return {
        recommends: [],
        details: "",
    }
  },
  async created() {
    await this.loadRecommends();
  },

  methods: {    

    onShowDetails(item) {
        this.details = item;
    },
    async loadRecommends() {
      const url = `${window.location.protocol}//${window.location.host}/recommended/all`;
      axios.get(url).then((response) => {
        if (response.data.error) {
          toastr.error(response.data.error, this.lang.Error + " !!");
        } else {
          this.recommends = response.data?.data;

          //slice array
          this.recommends = this.recommends.slice(-3);
          this.details = this.recommends[0];
        }
      });
    },
  }
}
</script>
<style scoped>

.card-details p {
    font-size: 16px;
}
.card-details .comment {
    font-weight: 500;
}

/* Container for the slider */
.recomeded-slider {
    display: flex;
    gap: 5px;
}

img.recomended-section-image {
    height: 175px;
    vertical-align: middle;
    width: 216px;
}

.recomended-item img {
    display: flex;
    width: 100px;
    transition: width 0.3s ease;
}
.recomended-item {
    display: flex;
    width: 140px;
    transition: width 0.3s ease;
    overflow: hidden;
}

.recomended-item:hover {
    width: 300px;
}

.recomended-item .item-detail {
    margin-left: 20px;
}

.recomended-item img {
    width: 145px;
    transition: width 0.3s ease;
}



</style>