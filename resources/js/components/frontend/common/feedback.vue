<template lang="">
    <div v-if="messages && messages.length"  class="container mb-5">
        <div class="card_new ">
            <h2 class="card-title_new">STRAIGHT FROM OUR FANS</h2>

           <div class="d-flex flex-column justify-content-between flex-md-row gap-3">

                <div class="feedback-card d-flex flex-column justify-content-center" v-for="(message, index) in messages" :key="index">
                    <img src="/images/img/star.png" alt="">
                    <h4>{{ message.name }}</h4>
                    <p style="color: #212529;" >{{ message.description }}</p>
                </div>
                
           </div>


        </div>
    </div>
</template>
<script>
export default {
    components: {
        
    },
    data() {
        return {
            messages: [],
        }
    },

    async created() {
        await this.loadAllData();
    },

    methods: {
        async loadAllData() {

            const baseUrl = `${window.location.protocol}//${window.location.host}`;
            
            const url = `${baseUrl}/flash-message/all`;

            axios.get(url).then((response) => {
                if (response.data.error) {
                    toastr.error(response.data.error, this.lang.Error + " !!");
                } else {
                    this.messages = response.data.data;
                    this.messages = this.messages.slice(-3);

                }
            });
        }
    }
}
</script>
<style scoped>
    .feedback-card {
        width: 355px;
        height: 182px;
        border-radius: 10px;
        border: 1px solid #707070;
        padding: 10px 20px;
        box-shadow: 9px 5px 5.5px 0px #00000017;
    }
    .feedback-card img {
        width: 120px;
    }
    .feedback-card h4 {
        font-size: 16px;
        line-height: 27px;
        margin-bottom: 10px;
        font-weight: bold;
    }
    .feedback-card p {
        font-size: 16px;
        line-height: 19px;
        font-weight: bold;
    }

@media screen and (max-width: 768px) {
    .feedback-card {
        width: 100%;
        height: 200px;
    }
    
}
</style>