<?php
defined('BASEPATH') or exit('Ação não permitida!');

class Produtos extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        //Existe uma sessão?
        if (!$this->ion_auth->logged_in()) {
            redirect('restrita/login');
        }
    }

    public function index()
    {
        $data = array(

            'titulo' => 'Produtos Cadastrados',

            'styles' => array(
                'bundles/datatables/datatables.min.css',
                'bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css'
            ),

            'scripts' => array(
                'bundles/datatables/datatables.min.js',
                'bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js',
                'bundles/jquery-ui/jquery-ui.min.js',
                'js/page/datatables.js'
            ),

            'produtos' => $this->produtos_model->get_all(),
        );
        $this->load->view('restrita/layout/header', $data);
        $this->load->view('restrita/produtos/index');
        $this->load->view('restrita/layout/footer');
    }

    public function core($produto_id = NULL)
    {
        $produto_id = (int) $produto_id;

        if (!$produto_id) {
            //Cadastrando
            $this->form_validation->set_rules('produto_nome', 'Nome do produto', 'trim|required|min_length[4]|max_length[240]|callback_valida_nome_produto');
            $this->form_validation->set_rules('produto_categoria_id', 'Categoria do produto', 'trim|required');
            $this->form_validation->set_rules('produto_marca_id', 'Marca do produto', 'trim|required');
            $this->form_validation->set_rules('produto_valor', 'Valor venda do produto', 'trim|required');
            $this->form_validation->set_rules('produto_peso', 'Peso do produto', 'trim|required|integer');
            $this->form_validation->set_rules('produto_altura', 'Altura do produto', 'trim|required|integer');
            $this->form_validation->set_rules('produto_largura', 'Largura do produto', 'trim|required|integer');
            $this->form_validation->set_rules('produto_comprimento', 'Comprimento do produto', 'trim|required|integer');
            $this->form_validation->set_rules('produto_quantidade_estoque', 'Quantidade em estoque', 'trim|required|integer');
            $this->form_validation->set_rules('produto_descricao', 'Descrição do produto', 'trim|required|min_length[10]|max_length[5000]');
            $fotos_produtos = $this->input->post('fotos_produtos');
            if (!$fotos_produtos) {
                $this->form_validation->set_rules('fotos_produtos', 'Imagens do produto', 'required');
            }
            if ($this->form_validation->run()) {
                //echo '<pre>';
                //print_r($this->input->post());
                //exit();
                $data = elements(
                    array(
                        'produto_nome',
                        'produto_categoria_id',
                        'produto_marca_id',
                        'produto_valor',
                        'produto_peso',
                        'produto_altura',
                        'produto_largura',
                        'produto_comprimento',
                        'produto_quantidade_estoque',
                        'produto_ativo',
                        'produto_destaque',
                        'produto_controlar_estoque',
                        'produto_descricao',
                    ),
                    $this->input->post()
                );
                // Remove a vírgula do valor
                $data['produto_valor'] = str_replace(',', '', $data['produto_valor']);

                // Criando o metalink do produto
                $data['produto_meta_link'] = url_amigavel($data['produto_nome']);

                // Código gerado
                $data['produto_codigo'] = $this->input->post('produto_codigo');

                $data = html_escape($data);

                $this->core_model->insert('produtos', $data, TRUE);

                // Recupera o ultimo id inserido
                $produto_id = $this->session->userdata('last_id');

                //Recuperar do post se veio fotos
                $fotos_produtos = $this->input->post('fotos_produtos');
                if ($fotos_produtos) {
                    $total_fotos = count($fotos_produtos);
                    for ($i = 0; $i < $total_fotos; $i++) {
                        $data = array(
                            'foto_produto_id' => $produto_id,
                            'foto_caminho' => $fotos_produtos[$i]
                        );
                        $this->core_model->insert('produtos_fotos', $data);
                    }
                }
                redirect('restrita/produtos');
            } else {
                // Erro de validação
                $data = array(

                    'titulo' => 'Cadastrar produto',

                    'styles' => array(
                        'jquery-upload-file/css/uploadfile.css',
                    ),

                    'scripts' => array(
                        'sweetalert2/sweetalert2.all.min.js',
                        'jquery-upload-file/js/jquery.uploadfile.min.js',
                        'jquery-upload-file/js/produtos.js',
                        'mask/jquery.mask.min.js',
                        'mask/custom.js'
                    ),
                    'codigo_gerado' => $this->core_model->generate_unique_code('produtos', 'numeric', 8, 'produto_codigo'),
                    'categorias' => $this->core_model->get_all('categorias', array('categoria_ativa' => 1)),
                    'marcas' => $this->core_model->get_all('marcas', array('marca_ativa' => 1))
                );

                $this->load->view('restrita/layout/header', $data);
                $this->load->view('restrita/produtos/core');
                $this->load->view('restrita/layout/footer');
            }
        } else {
            if (!$produto = $this->core_model->get_by_id('produtos', array('produto_id' => $produto_id))) {
                $this->session->set_flashdata('erro', 'Esse produto não foi encontrado!');
                redirect('restrita/produtos');
            } else {
                //Editando produto
                $this->form_validation->set_rules('produto_nome', 'Nome do produto', 'trim|required|min_length[4]|max_length[240]|callback_valida_nome_produto');
                $this->form_validation->set_rules('produto_categoria_id', 'Categoria do produto', 'trim|required');
                $this->form_validation->set_rules('produto_marca_id', 'Marca do produto', 'trim|required');
                $this->form_validation->set_rules('produto_valor', 'Valor venda do produto', 'trim|required');
                $this->form_validation->set_rules('produto_peso', 'Peso do produto', 'trim|required|integer');
                $this->form_validation->set_rules('produto_altura', 'Altura do produto', 'trim|required|integer');
                $this->form_validation->set_rules('produto_largura', 'Largura do produto', 'trim|required|integer');
                $this->form_validation->set_rules('produto_comprimento', 'Comprimento do produto', 'trim|required|integer');
                $this->form_validation->set_rules('produto_quantidade_estoque', 'Quantidade em estoque', 'trim|required|integer');
                $this->form_validation->set_rules('produto_descricao', 'Descrição do produto', 'trim|required|min_length[10]|max_length[5000]');
                if ($this->form_validation->run()) {
                    //echo '<pre>';
                    //print_r($this->input->post());
                    //exit();
                    $data = elements(
                        array(
                            'produto_nome',
                            'produto_categoria_id',
                            'produto_marca_id',
                            'produto_valor',
                            'produto_peso',
                            'produto_altura',
                            'produto_largura',
                            'produto_comprimento',
                            'produto_quantidade_estoque',
                            'produto_ativo',
                            'produto_destaque',
                            'produto_controlar_estoque',
                            'produto_descricao',
                        ),
                        $this->input->post()
                    );
                    // Remove a vírgula do valor
                    $data['produto_valor'] = str_replace(',', '', $data['produto_valor']);

                    // Criando o metalink do produto
                    $data['produto_meta_link'] = url_amigavel($data['produto_nome']);

                    $data = html_escape($data);

                    $this->core_model->update('produtos', $data, array('produto_id' => $produto_id));

                    // Exclui as imagens antigas do produto
                    $this->core_model->delete('produtos_fotos', array('foto_produto_id' => $produto_id));

                    //Recuperar do post se veio fotos
                    $fotos_produtos = $this->input->post('fotos_produtos');
                    if ($fotos_produtos) {
                        $total_fotos = count($fotos_produtos);
                        for ($i = 0; $i < $total_fotos; $i++) {
                            $data = array(
                                'foto_produto_id' => $produto_id,
                                'foto_caminho' => $fotos_produtos[$i]
                            );
                            $this->core_model->insert('produtos_fotos', $data);
                        }
                    }
                    redirect('restrita/produtos');
                } else {
                    // Erro de validação
                    $data = array(

                        'titulo' => 'Editar produto',

                        'styles' => array(
                            'jquery-upload-file/css/uploadfile.css',
                        ),

                        'scripts' => array(
                            'sweetalert2/sweetalert2.all.min.js',
                            'jquery-upload-file/js/jquery.uploadfile.min.js',
                            'jquery-upload-file/js/produtos.js',
                            'mask/jquery.mask.min.js',
                            'mask/custom.js'
                        ),

                        'produto' => $produto,
                        'fotos_produto' => $this->core_model->get_all('produtos_fotos', array('foto_produto_id' => $produto_id)),
                        'categorias' => $this->core_model->get_all('categorias', array('categoria_ativa' => 1)),
                        'marcas' => $this->core_model->get_all('marcas', array('marca_ativa' => 1))
                    );

                    $this->load->view('restrita/layout/header', $data);
                    $this->load->view('restrita/produtos/core');
                    $this->load->view('restrita/layout/footer');
                }
            }
        }
    }

    public function valida_nome_produto($produto_nome)
    {
        $produto_id = (int) $this->input->post('produto_id');
        if (!$produto_id) {
            //cadastrando
            if ($this->core_model->get_by_id('produtos', array('produto_nome' => $produto_nome))) {
                $this->form_validation->set_message('valida_nome_produto', 'Esse produto já existe');
                return false;
            } else {
                return true;
            }
        } else {
            //Editando
            if ($this->core_model->get_by_id('produtos', array('produto_nome' => $produto_nome, 'produto_id !=' => $produto_id))) {
                $this->form_validation->set_message('valida_nome_produto', 'Esse produto já existe');
                return false;
            } else {
                return true;
            }
        }
    }

    public function upload()
    {
        $config['upload_path'] = './uploads/produtos/';
        $config['allowed_types'] = 'jpg|png|jpeg';
        $config['max_size'] = 2048; //MAX 2MB
        $config['max_width'] = 1000;
        $config['max_height'] = 1000;
        $config['encrypt_name'] = TRUE;
        $config['max_filename'] = 200;
        $config['file_ext_tolower'] = TRUE;

        $this->load->library('upload', $config);
        if ($this->upload->do_upload('foto_produto')) {
            $data = array(
                'uploaded_data' => $this->upload->data(),
                'mensagem' => 'Imagem envida com sucesso!',
                'foto_caminho' => $this->upload->data('file_name'),
                'erro' => 0
            );
            // Resize image configuration
            $config['image_library'] = 'gd2';
            $config['source_image'] = './uploads/produtos/' . $this->upload->data('file_name');
            $config['new_image'] = './uploads/produtos/small/' . $this->upload->data('file_name');
            $config['width'] = 300;
            $config['height'] = 300;

            // Chama a biblioteca
            $this->load->library('image_lib', $config);
            // Faz o resize
            //$this->image_lib->resize();
            if (!$this->image_lib->resize()) {
                $data['erro'] = $this->image_lib->display_errors();
            }
        } else {
            $data = array(
                'mensagem' => $this->upload->display_errors(),
                'erro' => 5
            );
        }
        echo json_encode($data);
    }

    public function delete($produto_id = NULL)
    {
        $produto_id = (int) $produto_id;
        if(!$produto_id || !$this->core_model->get_by_id('produtos', array('produto_id' => $produto_id))) {
            $this->session->set_flashdata('erro', 'Esse produto não foi encontrado!');
            redirect('restrita/produtos');
        }

        if($this->core_model->get_by_id('produtos', array('produto_id' => $produto_id, 'produto_ativo' => 1))) {
            $this->session->set_flashdata('erro', 'Não é permitido excluir um produto ativo!');
            redirect('restrita/produtos');
        }
        // Recupera as fotos do produto antes da exclusão
        $fotos_produto = $this->core_model->get_all('produtos_fotos', array('foto_produto_id' => $produto_id));
        // Exclui o produto
        $this->core_model->delete('produtos', array('produto_id' => $produto_id));
        // Elimina as fotos do produto
        if($fotos_produto) {
            foreach($fotos_produto as $foto) {
                $foto_grande = FCPATH . 'uploads/produtos/' . $foto->foto_caminho;
                $foto_pequena = FCPATH . 'uploads/produtos/small' . $foto->foto_caminho;
                var_dump(file_exists($foto_grande)); // <!-- HORA DE DEBUGAR
	
	var_dump(file_exists($foto_pequena)); // <!-- HORA DE DEBUGAR
	
	exit;

                // Exclui as imagens
                if(file_exists($foto_grande) && file_exists($foto_pequena)) {
                    unlink($foto_grande);
                    unlink($foto_pequena);
                }
            }
            
        }
        redirect('restrita/produtos');
    }
}
