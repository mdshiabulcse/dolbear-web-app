<template>
    <div class="container">
        <h2 class="mt-3 mb-3" >Our Outlets</h2>
        <div  class=" col-12 col-md-12 outlets-details row mb-3">
            <div class="col-12 col-md-4" style="height: 350px; overflow: hidden;" v-html="selectedOutlet.map"></div>
            <div class="col-12 col-md-5 outlets-img " style="height: 350px;"  >
                <img class="outlets-img" :src="selectedOutlet.image" alt="">
            </div>

            <div class="col-12 col-md-3">
                <h3 class="outlate_title">{{ selectedOutlet.name }}</h3>
                <h5 class=" mt-4 outlate_address">{{ selectedOutlet.address }}</h5>
                <div class=" outlate_content">
                    <p class=" outlate_content">Contact No: {{ selectedOutlet.phone }}</p>
                    <p class=" outlate_content" v-html="selectedOutlet.description"></p>
                </div>
            </div>
        </div>
        <div class="outlets-list d-flex flex-wrap gap-2">
            <div
                v-for="(outlet, index) in stores"
                :key="index"
                class="outlets-item"
                :class="{ active: selectedOutletIndex === index }"
                @click="selectOutlet(outlet)"
            >
                <p class="outlate_dynamic_name">{{ outlet.name }}</p>
                <p class="outlate_dynamic_name">{{ outlet.address }}</p>
                <p class="outlate_dynamic_name">Contact No: {{ outlet.phone }}</p>
                <p class="outlate_dynamic_name" v-html="outlet.description"></p>
            </div>
            
        </div>
    </div>
</template>

<script>
export default {
    mounted() {
    this.getStores();
  },
    data() {
        return {
            selectedOutletIndex: "", 
            selectedOutlet: "", 
            stores: [],
        };
    },

    methods: {
        selectOutlet(store) {
            this.selectedOutlet = store;
            this.selectedOutletIndex = this.stores.indexOf(store);
        },
        getStores() {
            this.loading = true;
            let url = this.getUrl("store/allStore");
            axios
                .get(url)
                .then((response) => {
                if (response.data) {
                    this.stores = response.data;

                    this.selectOutlet(this.stores[0]);
                } else {
                    toastr.error(response.data.error, this.lang.Error + " !!");
                }

                this.loading = false;
                })
                .catch((error) => {
                this.loading = false;
                if (error.response.status == 422) {
                    this.errors = error.response.data.errors;
                }
                });
            },
    }
};
</script>

<style>
.outlets-details h2 {
    font-weight: 700;
    font-size: 26px;
    margin-bottom: 0;
    line-height: 40px;
}

.outlets-details p {
    margin-bottom: 0;
    font-size: 21px;
}
.store-map {
    width: 420px;
    height: 280px;
   
}
img.outlets-img {
    width: 100%;
    height:100%;
    object-fit: cover;
}

.outlets-item {
    width: 435px;
    height: 240px;
    border-radius: 5px;
    background: #FFFFFF;
    padding: 20px;
    text-align: center;
    cursor: pointer;
}

.outlets-item p {
    font-weight: 400;
}

.outlets-item:hover {
    background: #ECECEC;
}

.outlets-item.active {
    background: #ECECEC;
}

.outlate_title{
    font-size: 20px !important;
    overflow-wrap: break-word;
}

.outlate_content{
    font-size: 16px !important;
}

.outlate_dynamic_name{
    overflow-wrap: break-word;
}


@media screen and (max-width: 768px) {
    .outlets-item {
        width: 100%;
    }

    .outlate_title {
        font-size: 16px !important;
        margin-top: 15px;
    }
}


@media screen and (max-width: 435px) {
    .outlets-item {
        width: 100%;
    }
}




</style>
