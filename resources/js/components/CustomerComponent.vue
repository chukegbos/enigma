<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container mt-2">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <b-button variant="outline-primary" size="sm" v-b-modal.filter-modal><i class="fa fa-filter"></i> Filter</b-button>
                    <b-modal id="filter-modal" ref="filter" title="Filter" hide-footer>
                        <b-form @submit.stop.prevent="onFilterSubmit">

                            <b-form-group label="Name:" label-for="keyword">
                                <b-form-input id="name" v-model="filterForm.name" type="text"></b-form-input>
                            </b-form-group>
                            <b-button type="submit" variant="primary">Filter</b-button>
                        </b-form>
                    </b-modal>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                Cashiers
                            </div>

                            <div class="card-tools">
                                <button class="btn btn-success" @click="newModal">
                                    Add New
                                    <i class="fa fa-user fa-fw"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th width="400px">Address</th>
                                    <th>Modify</th>
                                </tr>

                                <tr v-for="user in users.data" :key="user.id">
                                    <td>{{ user.name }}</td>
                                    <td>{{ user.email }}</td>
                                    <td>{{ user.phone }}</td>
                                    <td>{{ user.address }}</td>
                                    <td>
                                        <a href="#" @click="editModal(user)">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        |
                                        <a href="#" @click="deleteUser(user.id)">
                                            <i class="fa fa-trash text-red"></i>
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="card-footer">
                            <pagination :data="users" @pagination-change-page="getResults"></pagination>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="addNewUser" tabindex="-1" role="dialog" aria-labelledby="addNewUserLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addNewUserLabel" v-show="!editMode">
                                Add New
                            </h5>
                            <h5 class="modal-title" id="addNewUserLabel" v-show="editMode">
                                Update User's Info
                            </h5>

                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <form @submit.prevent="editMode ? updateUser() : createUser()">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Full Name</label>
                                    <input v-model="form.name" type="text"
                                        name="name" class="form-control" placeholder="Fullname" :class="{'is-invalid': form.errors.has('name')}"/>
                                    <has-error :form="form" field="name"></has-error>
                                </div>

                                <div class="form-group">
                                    <label>Email Address</label>
                                    <input v-model="form.email" type="email" name="email" class="form-control" placeholder="Email Address"
                                        :class="{'is-invalid': form.errors.has('email')}"/>
                                    <has-error :form="form" field="email"></has-error>
                                </div>

                                <div class="form-group">
                                    <label>Phone Number</label>
                                    <input
                                        v-model="form.phone"
                                        type="tel"
                                        class="form-control"
                                        placeholder="Phone Number"
                                        :class="{
                                            'is-invalid': form.errors.has(
                                                'phone'
                                            )
                                        }"
                                    />
                                    <has-error :form="form" field="phone"></has-error>
                                </div>

                                <div class="form-group">
                                    <label>Address</label>
                                    <input v-model="form.address" type="text"
                                        class="form-control"
                                        placeholder="Email Address"
                                        :class="{'is-invalid': form.errors.has('address')}"
                                    />
                                    <has-error :form="form" field="address"></has-error>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">
                                    Close
                                </button>
                                <button v-show="editMode" type="submit" class="btn btn-success">
                                    Update
                                </button>
                                <button v-show="!editMode" type="submit" class="btn btn-primary">
                                    Create
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
        this.loadUsers();
        this.getUser();
    },

    data() {
        return {
            is_busy: false,
            editMode: false,
            model: {},
            users: {},
            user: "",
            form: new Form({
                id: "",
                name: "",
                email: "",
                phone: "",
                address: "",
            }),

            filterForm: {
                name: '',
            },
        };
    },

    methods: {
        getUser() {
            axios.get("/api/user").then(({ data }) => {
                this.user = data.user;
                this.usersss = data.user;
            });
        },

        getResults(page = 1) {
            axios.get("/api/customer?page=" + page)
            .then(response => {
                this.users = response.data;
            });
        },

        editModal(user) {
            (this.editMode = true), this.form.reset();
            $("#addNewUser").modal("show");
            this.form.fill(user);
            this.show_password = 2;
            this.form.store = user.store;
        },

        newModal() {
            (this.editMode = false), this.form.reset();
            $("#addNewUser").modal("show");
        },

        loadUsers() {
            if (this.is_busy) return;
            this.is_busy = true;

            axios.get("/api/customer", { params: this.filterForm })
            .then(({ data }) => {
                this.users = data.users;
            })
            .finally(() => {
                this.is_busy = false;
            });
        },

        onFilterSubmit()
        {
            this.loadUsers();
            this.getUser();
            this.$refs.filter.hide();
        },

        createUser() {
            this.$Progress.start();
            this.form.post("/api/customer")
            .then(() => {
                $("#addNewUser").modal("hide");

                Swal.fire(
                    "Created!",
                    "Cashier Created Successfully.",
                    "success"
                );
                this.loadUsers();
                this.getUser();
            })

            .catch();
        },

        updateUser() {
            this.form.put("/api/customer/" + this.form.id)

            .then(() => {
                $("#addNewUser").modal("hide");

                Swal.fire("Updated!", "Cashier Updated Successfully.", "success");
                this.$Progress.finish();
                this.loadUsers();
                this.getUser();
            })

            .catch();
        },

        deleteUser(id) {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then(result => {
                if (result.value) {
                    this.$Progress.start();
                    this.form.delete("/api/customer/" + id)
                    .then(() => {
                        Swal.fire(
                            "Deleted!",
                            "Cashier has been deleted.",
                            "success"
                        );
                        this.$Progress.finish();
                        this.loadUsers();
                        this.getUser();
                    })

                    .catch(() => {
                        Swal(
                            "Failed!",
                            "Ops, Something went wrong, try again.",
                            "Warning"
                        );
                    });
                }
            });
        }
    }
};
</script>
