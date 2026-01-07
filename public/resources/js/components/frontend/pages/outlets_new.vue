<template>
    <div class="container">
        <h2 class="mt-3 mb-3" >Our Outlets</h2>
        <div  class="outlets-details row mb-3">
            <div class="col-12 col-md-3" style="height: 350px;" v-html="selectedOutlet.map"></div>
            <img class="col-12 col-md-3 outlets-img" :src="selectedOutlet.image"  alt="" />

            <div class="col-12 col-md-5">
                <h2>{{ selectedOutlet.name }}</h2>
                <h4>{{ selectedOutlet.address }}</h4>
                <div class="mt-4">
                    <p>Contact No: {{ selectedOutlet.phone }}</p>
                    <p v-html="selectedOutlet.description"></p>
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
                <p>{{ outlet.name }}</p>
                <p>{{ outlet.address }}</p>
                <p>Contact No: {{ outlet.phone }}</p>
                <p v-html="outlet.description"></p>
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
    width: 420px;
    height: 280px;
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
    font-weight: 700;
}

.outlets-item:hover {
    background: #ECECEC;
}

.outlets-item.active {
    background: #ECECEC;
}
</style>
