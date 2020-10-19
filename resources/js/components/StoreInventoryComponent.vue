<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container pt-3">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Products
                            </div>
                        </div>
                      
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover">
                                <tr>
                                    <th>Product Name</th>
                                    <th>Selling Price (&#8358;)</th>
                                    <th>Cost Price (&#8358;)</th>
                                    <th>Quantity</th>
                                    <th v-if="!filterForm.store_code">Action</th>
                                </tr>

                                <tr v-for="inventory in inventories.data" :key="inventory.id">
                                    <td>{{ inventory.product_name }}</td>
                                    <td>{{ inventory.price }}</td>
                                    <td>{{ inventory.cost_price }}</td>
                                    <td>
                                        <span v-if="inventory.number<=0">0</span>
                                        <span v-else>{{ inventory.number }}</span>
                                    </td>  
                                    <td v-if="!filterForm.store_code">
                                        <span v-if="inventory.number<=0">Out of Stock</span>
                                        <a href="#" v-else  @click=addToCart(inventory)>
                                            <i class="fa fa-shopping-cart text-blue fa-2x"></i>
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="card-footer">
                            <pagination :data="inventories" @pagination-change-page="getResults"></pagination>        
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </b-overlay>
</template>

<script>
    export default {
        created() {
            this.loadInventory();
        },

        data() {
            return {
                is_busy: false,
                editMode: false,
                addMode: false,
                model: {},

                inventories: {},
                form: new Form({
                    id: "",
                    product_name: "",
                    price: "",
                    quantity: ""
                }),

                filterForm: {
                    store_code: '',
                },            
            };
        },

        methods: {
            getResults(page = 1) {
                axios.get('/api/store/inventory?page=' + page)
                .then(response => {
                    this.inventories = response.data;
                });
            },

            loadInventory() {
                if(this.is_busy) return;
                this.is_busy = true;

                this.filterForm.store_code = this.$route.params.store_code;

                if(this.filterForm.store_code ) {

                    axios.get('/api/store/inventory', { params: this.filterForm })

                    .then(({ data }) => {
                        this.inventories = data;
                    })
                    .finally(() => {
                        this.is_busy = false;
                    });
                }
                else
                {
                    axios.get("/api/store/inventory")
                    .then(({ data }) => {
                        this.inventories = data;
                    })
                    .finally(() => {
                        this.is_busy = false;
                    });
                }       
            },

            addToCart(inventory){
                axios.get('/api/cart/addCart/'+inventory.id)
                .then(({ data }) => {
                    this.$router.go({path: '/store/inventory'})
                });
            }
        }
    };
</script>
