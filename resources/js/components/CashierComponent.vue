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
                                    <th>Store</th>
                                    <th width="400px">Address</th>
                                    <th>Modify</th>
                                </tr>

                                <tr v-for="user in users.data" :key="user.id">
                                    <td>{{ user.name }}</td>
                                    <td>{{ user.email }}</td>
                                    <td>{{ user.phone }}</td>
                                    <td>{{ user.store_name}}</td>
                                    <td>{{ user.address }}</td>
                                    <td width="120px">
                                        <a href="#" @click="view(user)">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        |
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
                <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
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
                            <div class="modal-body row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Full Name</label>
                                        <input v-model="form.name" type="text"
                                            name="name" class="form-control" placeholder="Fullname" :class="{'is-invalid': form.errors.has('name')}"/>
                                        <has-error :form="form" field="name"></has-error>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Email Address</label>
                                        <input v-model="form.email" type="email" name="email" class="form-control" placeholder="Email Address"
                                            :class="{'is-invalid': form.errors.has('email')}"/>
                                        <has-error :form="form" field="email"></has-error>
                                    </div>
                                </div>

                                <div class="col-md-4">
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
                                </div>

                                <div class="col-md-12">
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

                                <div class="col-md-6">
                                    <div class="form-group" v-if="show_password==1">
                                        <label>Password</label>
                                        <input
                                            v-model="form.password"
                                            placeholder="Password"
                                            type="password"
                                            name="password"
                                            class="form-control"
                                            :class="{'is-invalid': form.errors.has('password')}"
                                        />
                                        <has-error :form="form" field="password"></has-error>
                                        <a href="#" class="pull-right" @click="setPassword"  v-show="editMode">Hide Password</a>
                                    </div>

                                    <a href="#" class="pull-right" @click="unsetPassword" v-else>Change Password</a>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Select Store</label>
                                        <b-form-select
                                            v-model="form.store"
                                            :options="stores"
                                            value-field="id"
                                            text-field="name"
                                            label="Assign Store"
                                        >
                                        <template v-slot:first>
                                            <b-form-select-option :value="null" disabled>
                                                -- Please select a storefront--
                                            </b-form-select-option>
                                        </template>
                                        </b-form-select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Next of Kin Name</label>
                                        <input v-model="form.next_of_kin" type="text"
                                            name="next_of_kin" class="form-control" placeholder="Next of Kin Name" :class="{'is-invalid': form.errors.has('next_of_kin')}"/>
                                        <has-error :form="form" field="next_of_kin"></has-error>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Next of Kin Address</label>
                                        <input v-model="form.next_of_kin_address" type="text" name="next_of_kin_address" class="form-control" placeholder="Next of Kin Address"
                                            :class="{'is-invalid': form.errors.has('next_of_kin_address')}"/>
                                        <has-error :form="form" field="next_of_kin_address"></has-error>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Next of Kin Phone</label>
                                        <input
                                            v-model="form.next_of_kin_phone"
                                            type="tel"
                                            class="form-control"
                                            placeholder="Next of Kin Phone Number"
                                            :class="{
                                                'is-invalid': form.errors.has(
                                                    'next_of_kin_phone'
                                                )
                                            }"
                                        />
                                        <has-error :form="form" field="next_of_kin_phone"></has-error>
                                    </div>
                                </div>


                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Upload Photo Image</label>
                                        <input type="file" @change="uploadImage" accept="image/*" name="image" class="form-control" required>
                                    </div>
                                </div>

                                <div class="col-md-6" v-if="form.image">
                                    <div class="my-2">
                                        <img :src="form.image" class="img-fluid" style="height:100px; width:150px">
                                    </div>
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
            stores: [],
            show_password: 1,
            user: "",
            form: new Form({
                id: "",
                name: "",
                email: "",
                phone: "",
                store: null,
                address: "",
                password: "",
                next_of_kin: '',
                next_of_kin_address: '',
                next_of_kin_phone: '',
                image: '',
            }),

            filterForm: {
                name: '',
                store: 0,
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
            axios.get("/api/cashier?page=" + page)
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
            this.form.image = '/img/photos/'+ user.image;
        },

        newModal() {
            (this.editMode = false), this.form.reset();
            $("#addNewUser").modal("show");
        },

        setPassword() {
            this.show_password = 2;
        },

        unsetPassword() {
            this.show_password = 1;
        },

        view(user) {            
            this.$router.push({ path: "/admin/user/" + user.id });
        },

        uploadImage(e){
            let file = e.target.files[0];
            let reader = new FileReader();
            if(file['size'] < 8388608){
              reader.onloadend = (file) => {
                this.form.image = reader.result;
                console.log(this.form.image);
              }
              reader.readAsDataURL(file);
            }else{
               Swal("Failed!", "Oops, You are uploading a large image, try again. Upload file less that 8MB", "Warning")
            }
        },

        loadUsers() {
            if (this.is_busy) return;
            this.is_busy = true;

            axios.get("/api/cashier", { params: this.filterForm })
            .then(({ data }) => {
                this.users = data.users;
                this.stores = data.stores;
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
            this.form.post("/api/cashier")
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
            this.form.put("/api/cashier/" + this.form.id)

            .then(() => {
                $("#addNewUser").modal("hide");

                Swal.fire("Updated!", "Cashier Updated Successfully.", "success");
                
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
                    this.form.delete("/api/cashier/" + id)
                    
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
