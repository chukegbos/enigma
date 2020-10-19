<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container">
            <div class="row">
                <div class="col-md-12 mt-3">
                    <div class="card card-widget widget-user">
                        <div class="card-header">
                            <div class="widget-user-header">
                                <h3 class="widget-user-username">{{ user.name }}</h3>
                                <h5 class="widget-user-desc">
                                    <span v-if="user.role==1">Cashier</span>
                                    <span v-else-if="user.role==2">Store Manager</span>
                                    <span v-else>Admin</span> ({{ user.store_name }})
                                </h5>
                            </div>


                            <div class="widget-user-image mt-2">
                                <img class="img-circle img-fluid" :src="'/img/photos/' + user.image" style="height:100px; width:150px" :alt="user.name">
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0 mt-5 text-center">
                            <div class="row">
                                <div class="col-md-3"></div>
                                <div class="col-md-6">
                                    <table class="table table-hover">
                                        <tr>
                                            <th>Name</th>
                                            <td>{{ user.name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Phone</th>
                                            <td>{{ user.phone }}</td>
                                        </tr>
                                        <tr>
                                            <th>Email</th>
                                            <td>{{ user.email }}</td>
                                        </tr>

                                        <tr>
                                            <th>Address</th>
                                            <td>{{ user.address }}</td>
                                        </tr>

                                        <tr>
                                            <th>Next of Kin</th>
                                            <td>{{ user.next_of_kin }}</td>
                                        </tr>
                                        <tr>
                                            <th>Next of Kin Phone</th>
                                            <td>{{ user.next_of_kin_phone }}</td>
                                        </tr>
                                        <tr>
                                            <th>Next of Kin Address</th>
                                            <td>{{ user.next_of_kin_address }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </b-overlay>
</template>

<script>
    export default {
        data(){
            return {
                is_busy: false,
                user: '',
            }
        },

        created(){
            this.loadPage();
        },

        methods: {
            loadPage(){
                if(this.is_busy) return;
                this.is_busy = true;

                let id = this.$route.params.id;
                axios.get('/api/user/' + id)
                .then((response) => {
                    this.user = response.data;
                    console.log(response.data)
                })

                .catch((err) => {
                    console.log(err);
                })

                .finally(() => {
                    this.is_busy = false;
                });
            }
        },
        
    }
</script>
