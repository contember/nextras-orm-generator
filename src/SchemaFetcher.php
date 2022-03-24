<?php declare(strict_types=1);

namespace Contember\NextrasOrmGenerator;

class SchemaFetcher
{
	private const SCHEMA_GQL = <<<'GQL'
		query {
			schema {
				entities {
					name

					unique {
						fields
					}

					fields {
						name
						nullable
						type

						... on _Column {
							defaultValue
							enumName
						}

						... on _Relation {
							side
							targetEntity
							ownedBy
							inversedBy
							onDelete
							orderBy {
								path
								direction
							}
						}
					}
				}

				enums {
					name
					values
				}
			}
		}
	GQL;


	public function fetchSchema(string $endpoint, string $token): \stdClass
	{
		$headers = [];
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Authorization: Bearer ' . $token;

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $endpoint);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query' => self::SCHEMA_GQL]));
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);
		$jsonResponse = json_decode($result);
		if (!empty($jsonResponse->errors)) {
			throw new \RuntimeException('Failed to fetch schema: ' . $result);
		}
		return $jsonResponse->data->schema;
	}
}
