<?php  

class ControllerModuleBrands extends Controller {

	protected function index($setting) {

		




		$start_time = microtime(true);	
		$this->load->model('catalog/category');	

		

		if (isset($this->request->get['path'])) {

			$path = '';

			



			/*foreach (explode('_', $this->request->get['path']) as $path_id) {

				if (!$path) {

					$path = $path_id;

				} else {

					$path .= '_' . $path_id;

				}

				

				$category_info = $this->model_catalog_category->getCategory($path_id);

				

				

			}*/
			$path = $this->request->get['path'];
			$ppath = explode("_",$path);
			$category_id=end($ppath);//$category_info['category_id'];





			$this->document->addScript('catalog/view/javascript/jquery/menu-collapsed.js');



			if (file_exists('catalog/view/theme/' . $this->config->get('config_template') . '/stylesheet/style_menu.css')) {

				$this->document->addStyle('catalog/view/theme/' . $this->config->get('config_template') . '/stylesheet/style_menu.css');

			} else {

				$this->document->addStyle('catalog/view/theme/default/stylesheet/style_menu.css');

			}









			if (isset($this->request->get['product_id'])) {

				$product_id = (int)$this->request->get['product_id'];

			} else {

				$product_id = 0;

			}



			$this->data['product_id'] = $product_id ;



			/*инфо про товар*/

			$this->load->model('catalog/product');



			$product_info = $this->model_catalog_product->getProduct($product_id);



			if ($product_info) {

				$this->data['manufacturer_id'] = $product_info['manufacturer_id'];

		 //$this->data['category_id'] =  $product_info['category_id'];

				$this->data['product_id']= $product_info['product_id'];



			}

















			/*масив всех бренедов магазина*/





			$this->data['manufacturers'] = array();



			$results = $this->model_catalog_manufacturer->getManufacturers();



			foreach ($results as $result) {





				$this->data['manufacturers'][] = array(
					'manufacturer_id' => $result['manufacturer_id'],
					'name' => $result['name'],
					'href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $result['manufacturer_id'])
					);

			}





			/*выбор брендов  с учетом  категории*/
			$where = '';
			$sql = "SELECT manufacturer_id, count(*) as cnt  
					FROM " . DB_PREFIX . "product p ";
			if ($category_id) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)";			
				$where = ' AND p2c.category_id = '.$category_id;
			}		
			$sql.= " WHERE p.status = '1' AND p.date_available <= NOW() ".$where;
			$sql.= " GROUP BY manufacturer_id ";
			$sql.= " HAVING cnt>0 ";

			//echo $sql;

			$mfs = array();						

			$query = $this->db->query($sql);

			$num_query = $this->db->query("SELECT FOUND_ROWS() AS `found_rows`");
			$this->FOUND_ROWS = intval($num_query->row['found_rows']);
		
			foreach ($query->rows as $result) {
				$mfs[$result['manufacturer_id']] = $result['cnt'];	
			}



			foreach ($this->data['manufacturers'] as $result) {

				$manufacturer_id= $result['manufacturer_id'];

		//echo '<br>'.$manufacturer_id= $result['manufacturer_id'].'-';

				/*

				if (isset($this->request->get['sort'])) {

					$sort = $this->request->get['sort'];

				} else {

					$sort = 'p.sort_order';

				}



				if (isset($this->request->get['order'])) {

					$order = $this->request->get['order'];

				} else {

					$order = 'ASC';

				}



				if (isset($this->request->get['page'])) {

					$page = $this->request->get['page'];

				} else { 

					$page = 1;

				}	



				if (isset($this->request->get['limit'])) {

					$limit = $this->request->get['limit'];

				} else {

					$limit = 100;

				}

				*/

				$this->data['products'] = array();





				$data = array(

					'filter_manufacturer_id' => $manufacturer_id, 

					'filter_category_id' => $category_id, 

				//	'sort'               => $sort,

				//	'order'              => $order,

					//'start'              => ($page - 1) * $limit,

				//	'limit'              => $limit

					);



				


				
				//$product_total = $this->model_catalog_product->getTotalProducts($data); 

				/*если есть товары по условию формируем масив товра*/

				if(isset($mfs[$manufacturer_id]))//$product_total)
				{

					$product_data = array();

					if($manufacturer_id == $this->data['manufacturer_id']){
				
						$results2 = $this->model_catalog_product->getProducts($data);					

						foreach ($results2 as $result2) {
							$product_data[] = array(
								'product_id'  => $result2['product_id'],
								'quantity'	  => $result2['quantity'],	
								'name'        => $result2['name'],
								'href'        => $this->url->link('product/product', 'path=' . $this->request->get['path'] . '&product_id=' . $result2['product_id'])
								);
						}
					}	





					$this->data['brands'][] = array(
						'product_total' => $mfs[$manufacturer_id],//$product_total,
						'manufacturer_id' => $result['manufacturer_id'],
						'name' => $result['name'],
						'href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $result['manufacturer_id']),
						'products'    => $product_data,
						'path_and_mf' => $result['manufacturer_id'].":".$this->request->get['path']				

						);	
				}
			}





			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/brands.tpl')) {

				$this->template = $this->config->get('config_template') . '/template/module/brands.tpl';

			} else {

				$this->template = 'default/template/module/brands.tpl';

			}



			$this->render();







		}
		$exec_time = microtime(true) - $start_time;

		echo "<!-- ".$exec_time." -->";

	}

	public function products_ajax() {
	//	$this->load->model('catalog/category');		
		$this->load->model('catalog/product');


		$path = $this->request->get['path'];

		$arr = explode(':',$path);
		if(count($arr)>1)
			$manufacturer_id = array_shift($arr);
		$path = $arr[0];
		
		$category_id = explode('_',$path);
		$category_id = end($category_id);
		$data = array(
			'filter_category_id' => $category_id,
			'filter_manufacturer_id' => $manufacturer_id
		);
		

		$results = $this->model_catalog_product->getProducts($data);
		
		$json = array();
		$out = '';

		if(count($results)){
			$out.='<ul>';	
			foreach ($results as $result) {
				$href = $this->url->link('product/product', 'path=' . $path . '&product_id=' . $result['product_id'] );
				$out.='<li><a '.(($result['quantity']>0)?"":"class='nosklad'").'href="'.$href.'">'.$result['name'].'</a></li>';
			}		
			$out.='</ul>';
			$json['success'] = $out;
		}
		
		$this->response->setOutput(json_encode($json));
	}	
	

}

?>