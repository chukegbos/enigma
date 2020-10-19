<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container mt-3">
            <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-8">
                    <div class="card">
                        <div v-if="products" class="card-body">
                            <form @submit.prevent="updateInventory()">
                                <div class="table-responsive">
                                    <table class="table table-condensed table-striped">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Quantity</th>
                                                <th>Unit Price (Naira)</th>
                                                <th>Price (Naira)</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <tr v-for="product in products" :key="product.id">
                                                <td>{{ product.item.product_name}}</td>
                                                <td>
                                                    <a href="#" @click=addOne(product.item.id) class="p-2"><i class="fa fa-plus fa-x"></i></a>
                                                    {{ product.quantity }}
                                                    <a href="#" @click=reduceOne(product.item.id) class="p-2"><i class="fa fa-minus fa-x"></i></a>
                                                </td>
                                                <td>{{ product.item.price}}</td>
                                                <td>{{ product.price }}</td>
                                                <td>
                                                    <a href="#" @click=reduceAll(product.item.id) class="p-2"><i class="fa fa-trash fa-x text-red"></i></a>
                                                </td>
                                            </tr>                                
                                        </tbody>

                                        <tfoot>
                                            <tr>
                                                <th class="text-right  pd-10"></th>
                                                <th class="text-right  pd-10">
                                                    Amount<br>
                                                    Discount({{ discount }}%)<br>
                                                    Total Price
                                                </th>
                                                <th class="text-right  pd-10 ">
                                                    {{ totalPrice }}<br>
                                                    {{ form.discount }}
                                                    <br>
                                                    N{{ form.amount }}
                                                </th>
                                                <th>
                                                    <br>
                                                        <small>
                                                            <a href="#" @click="ApplyModal">Apply Discount</a>
                                                        </small>
                                                    </span>
                                                </th>
                                            </tr>
                                        </tfoot>

                                    </table>  
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Select Mode of Payment</label>
                                            <select v-model="form.selected" class="form-control">
                                                <option v-for="option in options" v-bind:value="option.value">
                                                    {{ option.text }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Amount Paid</label>
                                            <input type="number" class="form-control" v-model="form.paid" :max="form.amount">
                                        </div>
                                    </div>

                                    <div class="col-md-4" v-if="!setUser">
                                        <div class="form-group">
                                            <label>Select Customer</label>
                                            <b-form-select
                                                v-model="form.customer_id"
                                                :options="customers"
                                                value-field="id"
                                                text-field="name"
                                            >
                                                <template v-slot:first>
                                                    <b-form-select-option :value="null" disabled>
                                                        -- Select customer--
                                                    </b-form-select-option>
                                                </template>
                                            </b-form-select>
                                            <small><a href="#" @click=addUser>Add User</a></small>
                                        </div>
                                    </div>
                                

                                    <div class="col-md-4" v-if="setUser">
                                        <div class="form-group">
                                            <label>Customer Name</label>
                                            <input type="text" class="form-control" v-model="form.name">
                                        </div>
                                    </div>

                                    <div class="col-md-6" v-if="setUser">
                                        <div class="form-group">
                                            <label>Customer Phone</label>
                                            <input type="tel" class="form-control" v-model="form.phone">
                                        </div>
                                    </div>

                                    <div class="col-md-6" v-if="setUser">
                                        <div class="form-group">
                                            <label>Customer Email</label>
                                            <input type="email" class="form-control" v-model="form.email">
                                        </div>
                                    </div>

                                    <div class="col-md-12" v-if="setUser">
                                        <div class="form-group">
                                            <label>Customer Address</label>
                                            <input type="text" class="form-control" v-model="form.address">
                                        </div>
                                        <small><a href="#" @click=removeUser>Remove</a></small>
                                    </div>

                                    <div class="col-md-6">
                                    
                                    </div>

                                    <div class="col-md-6">
                                        <button type="submit" class="btn btn-success">Checkout</button>
                                    </div>
                                </div>
                            </form>
                        </div> 
                        <div v-else class="card-body">
                            <div class="alert alert-info text-center">
                                No item in cart!!!
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="discountModal" tabindex="-1" role="dialog" aria-labelledby="addNewstoreLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Percentage Discount</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form @submit.stop.prevent="onApply">
                            <div class="modal-body">
                                <div class="form-group">
                                    <input v-model="applied_discount.percent" type="number" class="form-control" :max="user.sale_percent"/>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">
                                    Apply
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </b-overlay>
</template>

<script>
    export default {
        created() {
            this.loadCart();
            this.getUser();
        },

        data() {
            return {
                is_busy: false,
                totalPrice: '',
                products: '',
                options: [
                    { text: 'Cash', value: 'Cash' },
                    { text: 'Transfer', value: 'Transfer' },
                    { text: 'Cheque', value: 'Cheque' },
                    { text: 'POS', value: 'POS' }
                ],
                customers: [],
                form: new Form({
                    selected: 'Cash',
                    customer_id: null,
                    name: '',
                    phone: '',
                    email: '',
                    address: '',
                    percent_discount: '',
                    discount: '',
                    amount: '',
                    paid: '',
                }),

                applied_discount: {
                    percent: '',
                },
                setUser: '',
                user: '',
                store: '',
                discount: '0',
            };
        },

        methods: {
            getUser() {
                axios.get("/api/user")
                .then(({ data }) => {
                    this.user = data.user;
                    this.store = data.store;
                    this.customers = data.customers;
                });
            },

            loadCart() {
                if(this.is_busy) return;
                this.is_busy = true;
                axios.get('/api/cart/getCart')
                .then(({ data }) => {
                    this.totalPrice = data.totalPrice;
                    this.form.paid = this.totalPrice;
                    this.form.percent_discount = 0;
                    this.form.discount = 0;
                    this.form.amount = this.totalPrice;
                    this.products = data.products;
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            ApplyModal() {
                this.applied_discount.percent = this.user.sale_percent;

                $("#discountModal").modal("show");
            },

            addUser() {
                this.setUser = 1;
            },

            removeUser() {
                this.setUser = '';
            },

            onApply()
            {   
                this.discount = this.applied_discount.percent;
                
                this.form.discount = (this.applied_discount.percent/100)*this.totalPrice;
                this.form.amount = this.totalPrice - this.form.discount;
                this.form.percent_discount = this.applied_discount.percent;
                $("#discountModal").modal("hide");
            },

            addOne(id) {
                axios.get('/api/cart/addOne/'+id)
                .then(({ data }) => {
                    this.loadCart();
                });
            },

            reduceOne(id) {
                axios.get('/api/cart/reduceOne/'+id)
                .then(({ data }) => {
                    this.loadCart();
                });
            },

            reduceAll(id) {
                console.log(id)
                axios.get('/api/cart/reduceAll/'+id)
                .then(({ data }) => {
                    this.loadCart();
                });
            },

            updateInventory(){
                axios.post('/api/cart/checkout', this.form)
                .then(()=>{
                    Swal.fire(
                        'Created!',
                        'Order Completed.',
                        'success'
                    )
                    this.$router.go({path: '/store/inventory/' + this.store.code})
                })

                .catch((error) => {
                    Swal.fire(
                        'Failed!',
                        'Error checking out',
                        'error'
                    )
                });       
            },
        },
    };
</script>
