<?php


use Phinx\Migration\AbstractMigration;

class Oauth extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        // OAuth2 tables
        $this->table('oauth_clients', ['id' => false, 'primary_key' => 'client_id'])
            ->addColumn('client_id', 'string',
                [
                    'limit' => '80',
                    'null' => false,
                ])
            ->addColumn('client_secret', 'string',
                [
                    'limit' => '80',
                    'null' => true
                ])
            ->addColumn('redirect_uri', 'string',
                [
                    'limit' => '2000',
                    'null' => true
                ])
            ->addColumn('grant_types', 'string',
                [
                    'limit' => '80',
                    'null' => true
                ])
            ->addColumn('scope', 'string',
                [
                    'limit' => '2000',
                    'null' => true
                ])
            ->addColumn('user_id','integer', ['null' => true])
            ->addForeignKey('user_id', 'user', 'id', ['update'=> 'CASCADE', 'delete'=> 'CASCADE'])
            ->save();

        $this->table('oauth_access_tokens', ['id' => false, 'primary_key' => 'access_token'])
            ->addColumn('access_token', 'string',
                [
                    'limit' => '40',
                    'null' => false,
                ])
            ->addColumn('client_id', 'string',
                [
                    'limit' => '80',
                    'null' => false
                ])
            ->addColumn('user_id','integer', ['null' => true])
            ->addColumn('scope', 'string',
                [
                    'limit' => '2000',
                    'null' => true
                ])
            ->addColumn('expires','timestamp',
                [
                    'null' => false
                ])
            ->addForeignKey('user_id', 'user', 'id', ['update'=> 'CASCADE', 'delete'=> 'CASCADE'])
            ->addForeignKey('client_id', 'oauth_clients', 'client_id', ['update'=> 'CASCADE', 'delete'=> 'CASCADE'])
            ->save();

        $this->table('oauth_authorization_codes', ['id' => false, 'primary_key' => 'authorization_code'])
            ->addColumn('authorization_code', 'string',
                [
                    'limit' => '40',
                    'null' => false,
                ])
            ->addColumn('client_id', 'string',
                [
                    'limit' => '80',
                    'null' => false
                ])
            ->addColumn('user_id','integer', ['null' => true])
            ->addColumn('redirect_uri', 'string',
                [
                    'limit' => '2000',
                    'null' => true
                ])
            ->addColumn('expires','timestamp',
                [
                    'null' => false
                ])
            ->addColumn('scope', 'string',
                [
                    'limit' => '2000',
                    'null' => true
                ])
            ->addColumn('id_token', 'string',
                [
                    'limit' => '2000',
                    'null' => true
                ])
            ->addForeignKey('user_id', 'user', 'id', ['update'=> 'CASCADE', 'delete'=> 'CASCADE'])
            ->addForeignKey('client_id', 'oauth_clients', 'client_id', ['update'=> 'CASCADE', 'delete'=> 'CASCADE'])
            ->save();

        $this->table('oauth_refresh_tokens', ['id' => false, 'primary_key' => 'refresh_token'])
            ->addColumn('refresh_token', 'string',
                [
                    'limit' => '40',
                    'null' => false,
                ])
            ->addColumn('client_id', 'string',
                [
                    'limit' => '80',
                    'null' => false
                ])
            ->addColumn('user_id','integer', ['null' => true])
            ->addColumn('expires','timestamp',
                [
                    'null' => false
                ])
            ->addColumn('scope', 'string',
                [
                    'limit' => '2000',
                    'null' => true
                ])
            ->addForeignKey('user_id', 'user', 'id', ['update'=> 'CASCADE', 'delete'=> 'CASCADE'])
            ->addForeignKey('client_id', 'oauth_clients', 'client_id', ['update'=> 'CASCADE', 'delete'=> 'CASCADE'])
            ->save();

        $this->table('oauth_scopes')
            ->addColumn('scope', 'string',
                [
                    'limit' => '2000',
                    'null' => false
                ])
            ->addColumn('is_default', 'boolean', ['null' => true])
            ->save();

        $this->table('oauth_jwt', ['id' => false, 'primary_key' => 'client_id'])
            ->addColumn('client_id', 'string',
                [
                    'limit' => '80',
                    'null' => false
                ])
            ->addColumn('subject', 'string',
                [
                    'limit' => '80',
                    'null' => true
                ])
            ->addColumn('public_key', 'string',
                [
                    'limit' => '2000',
                    'null' => false
                ])
            ->addForeignKey('client_id', 'oauth_clients', 'client_id', ['update'=> 'CASCADE', 'delete'=> 'CASCADE'])
            ->save();

        $this->table('oauth_jti')
            ->addColumn('issuer', 'string',
                [
                    'limit' => '80',
                    'null' => false
                ])
            ->addColumn('subject', 'string',
                [
                    'limit' => '80',
                    'null' => true
                ])
            ->addColumn('audience', 'string',
                [
                    'limit' => '80',
                    'null' => true
                ])
            ->addColumn('expires','timestamp',
                [
                    'null' => false
                ])
            ->addColumn('jti', 'string',
                [
                    'limit' => '2000',
                    'null' => false
                ])
            ->addForeignKey('issuer', 'oauth_clients', 'client_id', ['update'=> 'CASCADE', 'delete'=> 'CASCADE'])
            ->save();

        $this->table('oauth_public_keys')
            ->addColumn('client_id', 'string',
                [
                    'limit' => '80',
                    'null' => true
                ])
            ->addColumn('public_key', 'string',
                [
                    'limit' => '2000',
                    'null' => true
                ])
            ->addColumn('private_key', 'string',
                [
                    'limit' => '2000',
                    'null' => true
                ])
            ->addColumn('encryption_algorithm', 'string',
                [
                    'default' => 'RS256',
                    'limit' => '100'
                ])
            ->addForeignKey('client_id', 'oauth_clients', 'client_id', ['update'=> 'CASCADE', 'delete'=> 'CASCADE'])
            ->save();
    }
}
