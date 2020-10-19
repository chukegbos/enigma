<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <b-button variant="outline-primary" size="sm" v-b-modal.filter-modal><i class="fa fa-filter"></i> Filter</b-button>
                    <b-modal id="filter-modal" ref="filter" title="Filter" hide-footer>
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

                            <b-form-group label="Store:" v-if="user.role==3">
                                <b-form-select v-model="filterForm.store" :options="stores" value-field="id" text-field="name">
                                    <template v-slot:first>
                                        <b-form-select-option :value="0">
                                            All
                                        </b-form-select-option>
                                    </template>
                                </b-form-select>
                            </b-form-group>

                            <b-button type="submit" variant="primary">Search</b-button>
                        </b-form>
                    </b-modal>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="info-box bg-blue">
                        <span class="info-box-icon"><i class="fa fa-calculator"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Credit</span>
                            <span class="info-box-number">{{ query_credit }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="info-box bg-blue">
                        <span class="info-box-icon"><i class="fa fa-calculator"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Debit</span>
                            <span class="info-box-number">{{ query_debit }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-blue">
                        <span class="info-box-icon"><i class="fa fa-calculator"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Balance</span>
                            <span class="info-box-number">{{ query_credit - query_debit }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                Account Statement
                            </div>
                        </div>
                        <div v-if="accounts.data.length">
                            <div class="card-body table-responsive p-0">
                                <table class="table table-hover">
                                    <tr>
                                        <th>ID</th>
                                        <th>Type</th>
                                        <th>Store</th>
                                        <th>Purpose</th>
                                        <th>Amount(&#8358;)</th>
                                        <th>Date</th>
                                    </tr>

                                    <tr
                                        v-for="account in accounts.data"
                                        :key="account.id"
                                    >
                                        <td>{{ account.ref_id }}</td>
                                        <td>
                                            <span v-if="account.type==1">Credit</span>
                                            <span v-else>Debit</span>
                                        </td>
                                        <td>{{ account.store }}</td>
                                        <td>{{ account.purpose }}</td>
                                        <td>{{ account.amount }}</td>
                                        <td>{{ account.main_date | myDate}}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="card-footer">
                                <pagination :data="accounts" @pagination-change-page="getResults"></pagination>
                            </div>
                        </div>

                        <div v-else class="p-5">
                            <div class="alert alert-info text-center">
                                No Account.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </b-overlay>
</template>

<script>
    import moment from 'moment'
    export default {
        data() {
            return {
                is_busy: false,
                model: {},
              
                accounts: [],
                categories: [],
                stores: [],
                user: "",
                accounts: {
                    data: '',
                },

                filterForm: {
                    store: 0,
                    start_date: moment().subtract(30, 'days').format("YYYY-MM-DD"),
                    end_date: moment().format("YYYY-MM-DD"),
                },
                query_debit: '',
                query_credit: '',
            };
        },

        created() {
            this.loadAccount();
            this.getUser();
        },

        methods: {
            getUser() {
                axios.get("/api/user")
                .then(({ data }) => {
                    this.user = data.user;
                });
            },

            getResults(page = 1) {
                axios.get("/api/account/balance?page=" + page).then(response => {
                    this.accounts = response.data;
                });
            },

            loadAccount() {
                if (this.is_busy) return;
                this.is_busy = true;
                
                axios.get("/api/account/balance", { params: this.filterForm })
                .then(({ data }) => {
                    this.accounts = data.accounts;
                    this.categories = data.categories;
                    this.stores = data.stores;
                    this.query_credit = data.credit_account;
                    this.query_debit = data.debit_account;
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            onFilterSubmit()
            {
                this.loadAccount();
                this.getUser();
                this.$refs.filter.hide();
            },
        },
    };
</script>
