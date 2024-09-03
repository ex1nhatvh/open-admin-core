<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminTables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        return config('admin.database.connection') ?: config('database.default');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('admin.database.users_table'), function (Blueprint $table) {
            $table->comment('справочник пользователей');
            $table->id()->comment('инкремент ID');
            $table->string('username', 190)->comment('логин')->unique();
            $table->string('password', 60)->comment('пароль');
            $table->string('name')->comment('имя пользователя');
            $table->string('avatar')->comment('аватар')->nullable();
            $table->string('remember_token', 100)->comment('токена функционала «Запомнить меня»')->nullable();
            $table->timestamps();
        });

        Schema::create(config('admin.database.roles_table'), function (Blueprint $table) {
            $table->comment('справочник ролей');
            $table->id()->comment('инкремент ID');
            $table->string('name', 50)->comment('имя роли')->unique();
            $table->string('slug', 50)->comment('слаг - уникальная строка/идентификатор')->unique();
            $table->timestamps();
        });

        Schema::create(config('admin.database.permissions_table'), function (Blueprint $table) {
            $table->comment('разрешения');
            $table->id()->comment('инкремент ID');
            $table->string('name', 50)->comment('имя')->unique();
            $table->string('slug', 50)->comment('слаг - уникальная строка/идентификатор')->unique();
            $table->string('http_method')->comment('название метода')->nullable();
            $table->text('http_path')->comment('путь к методу')->nullable();
            $table->timestamps();
        });

        Schema::create(config('admin.database.menu_table'), function (Blueprint $table) {
            $table->comment('меню');
            $table->increments('id')->comment('инкремент ID');
            $table->integer('parent_id')->comment('родительский ID в этой же таблице')->default(0);
            $table->integer('order')->comment('позиция в сортировке')->default(0);
            $table->string('title', 50)->comment('название');
            $table->string('icon', 50)->comment('иконка');
            $table->string('uri')->comment('идентификатор ресурса')->nullable();
            $table->string('permission')->comment('разрешение')->nullable();
            $table->timestamps();
        });

        Schema::create(config('admin.database.role_users_table'), function (Blueprint $table) {
            $table->comment('связь ролей и пользователей');
            $table->bigInteger('role_id')->comment('ссылочный ключ на инкремент ID в таблице roles');
            $table->bigInteger('user_id')->comment('ссылочный ключ на инкремент ID в таблице users');
            $table->index(['role_id', 'user_id']);
            $table->timestamps();
            $table->foreign('role_id')
                ->references('id')
                ->on(config('admin.database.roles_table'))
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('user_id')
                ->references('id')
                ->on(config('admin.database.users_table'))
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        Schema::create(config('admin.database.role_permissions_table'), function (Blueprint $table) {
            $table->comment('связь ролей и разрешений');
            $table->bigInteger('role_id')->comment('ссылочный ключ на инкремент ID в таблице roles');
            $table->bigInteger('permission_id')->comment('ссылочный ключ на инкремент ID в таблице permissions');
            $table->index(['role_id', 'permission_id']);
            $table->timestamps();
            $table->foreign('role_id')
                ->references('id')
                ->on(config('admin.database.roles_table'))
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('permission_id')
                ->references('id')
                ->on(config('admin.database.permissions_table'))
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        Schema::create(config('admin.database.user_permissions_table'), function (Blueprint $table) {
            $table->comment('связь пользователей и разрешений');
            $table->bigInteger('user_id')->comment('ссылочный ключ на инкремент ID в таблице user');
            $table->bigInteger('permission_id')->comment('ссылочный ключ на инкремент ID в таблице permissions');
            $table->index(['user_id', 'permission_id']);
            $table->timestamps();
            $table->foreign('user_id')
                ->references('id')
                ->on(config('admin.database.users_table'))
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('permission_id')
                ->references('id')
                ->on(config('admin.database.permissions_table'))
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        Schema::create(config('admin.database.role_menu_table'), function (Blueprint $table) {
            $table->comment('связь ролей и меню');
            $table->bigInteger('role_id')->comment('ссылочный ключ на инкремент ID в таблице role');
            $table->bigInteger('menu_id')->comment('ссылочный ключ на инкремент ID в таблице menu');
            $table->index(['role_id', 'menu_id']);
            $table->timestamps();
            $table->foreign('role_id')
                ->references('id')
                ->on(config('admin.database.roles_table'))
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('menu_id')
                ->references('id')
                ->on(config('admin.database.menu_table'))
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        Schema::create(config('admin.database.operation_log_table'), function (Blueprint $table) {
            $table->comment('журнал событий');
            $table->id()->comment('инкремент ID');;
            $table->bigInteger('user_id')->comment('ссылочный ключ на инкремент ID в таблице user');
            $table->string('path')->comment('путь к методу');
            $table->string('method', 10)->comment('тип метода');
            $table->ipAddress('ip')->comment('IP адрес');
            $table->text('input')->comment('параметры по событию');
            $table->index('user_id');
            $table->timestamps();
            $table->foreign('user_id')
                ->references('id')
                ->on(config('admin.database.users_table'))
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('admin.database.operation_log_table'));
        Schema::dropIfExists(config('admin.database.role_menu_table'));
        Schema::dropIfExists(config('admin.database.role_permissions_table'));
        Schema::dropIfExists(config('admin.database.role_users_table'));
        Schema::dropIfExists(config('admin.database.user_permissions_table'));
        Schema::dropIfExists(config('admin.database.menu_table'));
        Schema::dropIfExists(config('admin.database.permissions_table'));
        Schema::dropIfExists(config('admin.database.roles_table'));
        Schema::dropIfExists(config('admin.database.users_table'));
    }
}
