<template>
    <div class="container mt-4">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <b-button variant="outline-primary" size="sm" v-b-modal.filter-modal><i class="fa fa-filter"></i> Filter</b-button>
                        <b-modal id="filter-modal" ref="filter" title="Report Filter" hide-footer>
                            <b-form @submit.stop.prevent="onFilterSubmit">

                                <div class="row">
                                    <div class="col-lg-6">
                                        <b-form-group label="Start Date:" label-for="Start Date">
                                            <b-form-datepicker v-model="filterForm.start_date" placeholder="Start date"
                                                :date-format-options="{ year: 'numeric', month: 'short', day: '2-digit', weekday: 'short' }">
                                            </b-form-datepicker>
                                        </b-form-group>
                                    </div>

                                    <div class="col-lg-6">
                                        <b-form-group label="End Date:" label-for="End Date">
                                            <b-form-datepicker v-model="filterForm.end_date" placeholder="End date"
                                                :date-format-options="{ year: 'numeric', month: 'short', day: '2-digit', weekday: 'short' }">
                                            </b-form-datepicker>
                                        </b-form-group>
                                    </div>
                                </div>

                                <b-form-group label="Store:" label-for="store">
                                    <b-form-select
                                        v-model="filterForm.store"
                                        :options="stores"
                                        value-field="id"
                                        text-field="name"
                                        label="Assign Store"
                                    >
                                        <template v-slot:first>
                                            <b-form-select-option :value="0">
                                                All
                                            </b-form-select-option>
                                        </template>
                                    </b-form-select>
                                </b-form-group>

                                <b-form-group label="Mode of Payment:">
                                    <b-form-select
                                        v-model="filterForm.mode_of_payment"
                                        :options="payments"
                                        value-field="value"
                                        text-field="text"
                                    >
                                        <template v-slot:first>
                                            <b-form-select-option :value="0">
                                                All
                                            </b-form-select-option>
                                        </template>
                                    </b-form-select>
                                </b-form-group>

                                <b-button type="submit" variant="primary">Generate Report</b-button>
                            </b-form>
                        </b-modal>
                    </div>
                </div>
                <b-overlay :show="is_busy" rounded="sm">
                    <div v-if="summary" class="row">
                        <div class="col-md-6">
                            <div class="info-box bg-yellow">
                                <span class="info-box-icon"><i class="fa fa-calculator"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Orders</span>
                                    <span class="info-box-number">{{ summary.total_orders }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-box bg-red">
                                <span class="info-box-icon"><i class="fa fa-money"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Amount</span>
                                    <span class="info-box-number">{{ summary.total_amount }} Naira</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div v-if="report_items.data.length" style="text-align:center">
                                    <div class="card-body table-responsive p-0">
                                        <table class="table table-hover">
                                            <tr>
                                                <th>Sale ID</th>
                                                <th>Store</th>
                                                <th>Sales Rep</th>
                                                <th>Mode of Payment</th>
                                                <th>Est Amount (Naira)</th>
                                                <th>Discount(%)</th>
                                                <th>Amount Discounted (Naira)</th>
                                                <th>Total Price (Naira)</th>
                                                <th>Amount Paid (Naira)</th>
                                                <th>Date</th>
                                            </tr>

                                            <tr
                                                v-for="order in report_items.data"
                                                :key="order.id"
                                            >
                                                <td>
                                                    {{ order.sale_id }} <br>
                                                    <a href="#" @click=viewItems(order)>View</a>
                                                </td>
                                                <td>{{ order.store_name }}</td>
                                                <td>{{ order.user_name }}</td>
                                                <td>{{ order.mop | capitalize }}</td>
                                                <td>{{ order.initialPrice  }}</td>
                                                <td>{{ order.percent_discount  }}</td>
                                                <td>{{ order.discount  }}</td>
                                                <td>{{ order.totalPrice  }}</td>
                                                <td>{{ order.amount_paid  }}</td>
                                                <td>{{ order.created_at | myDate }}<br>{{ order.created_at }}</td> 
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="card-footer">
                                        <pagination :data="report_items" @pagination-change-page="getResults">
                                        </pagination>
                                    </div>
                                </div>

                                <div v-else class="p-5">
                                    <div class="alert alert-info text-center">
                                        No Order(s)
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div 
                            class="modal fade" 
                            id="getItem" 
                            tabindex="-1" 
                            role="dialog" 
                            aria-labelledby="addNewFridgeLabel" 
                            aria-hidden="true">

                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="addNewFridgeLabel">Items</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>

                                    <div class="modal-body">                         
                                        <div class="table-responsive">
                                            <table class="table table-condensed table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Product</th>
                                                        <th>Quantity</th>
                                                        <th>Unit Price (Naira)</th>
                                                        <th>Price (Naira)</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    <tr v-for="item in items" :key="item.id">
                                                        <td>{{ item.item.product_name}}</td>
                                                        <td>
                                                            {{ item.quantity }}
                                                        </td>
                                                        <td>{{ item.item.price}}</td>
                                                        <td>{{ item.price }}</td>
                                                    </tr>                                
                                                </tbody>

                                                <tfoot>
                                                    <tr>
                                                        <th></th>
                                                        <th></th>
                                                        <th>Total Price</th>
                                                        <th><span class="vd_green font-sm font-normal"> N{{ totalPrice }}</span></th>
                                                    </tr>
                                                </tfoot>

                                            </table>  
                                        </div>                           
                                    </div>
                                  
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </b-overlay>
            </div>
        </div>
    </div>
</template>

<script>
    import moment from 'moment'
    export default {
        created() {
            this.loadSales();
        },

        data() {
            return {
                filterForm: {
                    start_date: moment().subtract(30, 'days').format("YYYY-MM-DD"),
                    end_date: moment().format("YYYY-MM-DD"),
                    store: 0,
                    mode_of_payment: 0,
                },

                payments: [
                  { value: 'Cash', text: 'Cash' },
                  { value: 'POS', text: 'POS' },
                  { value: 'Transfer', text: 'Transfer' },
                ],
                stores: {},
                is_busy: false,
                report_items: null,
                summary: null,
                report_items: {
                    data: '',
                },

                items: {},
                totalPrice: '',
            };
        },

        methods: {
            getResults(page = 1) {
                if(this.is_busy) return;
                this.is_busy = true;

                axios.get('/api/store/reports?page=' + page, { params: this.filterForm })
                .then(response => {
                    this.report_items = response.data.report_data;
                    this.summary = response.data.summary;
                })
                .catch((err) => {
                        console.log(err);
                })

                .finally(() => {
                    this.is_busy = false;
                });
            },

            loadSales() {
                if(this.is_busy) return;
                this.is_busy = true;

                this.filterForm.store_code = this.$route.params.code;
                axios.get('/api/store/reports', { params: this.filterForm })
                .then((response) => {
                    this.report_items = response.data.report_data;
                    this.summary = response.data.summary;
                    this.stores = response.data.stores;
                })

                .catch((err) => {
                    console.log(err);
                })

                .finally(() => {
                    this.is_busy = false;
                });
            },

            onFilterSubmit()
            {
                this.loadSales();
                this.$refs.filter.hide();
            },

            viewItems(order) {
                this.items = order.cart.items;
                this.totalPrice = order.cart.totalPrice;
                $('#getItem').modal('show');
            },
        },
    };
</script>

<style>
    .list_product{
        list-style: none;
    }
</style>
