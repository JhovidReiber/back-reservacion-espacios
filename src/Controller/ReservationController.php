<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Space;
use App\Entity\User;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

class ReservationController extends AbstractController
{   
     /**
     * @OA\Post(
     *     path="/api/reservations",
     *     summary="Crear una nueva reserva",
     *     description="Crear una nueva reserva con los datos proporcionados",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="user", type="integer", description="ID del usuario", example=1),
     *                 @OA\Property(property="space", type="integer", description="ID del espacio", example=1),
     *                 @OA\Property(property="name_event", type="string", description="Nombre del evento", example="Concierto de verano"),
     *                 @OA\Property(property="date_start", type="string", format="date-time", description="Fecha de inicio del evento", example="2025-07-11T05:00:00.000Z"),
     *                 @OA\Property(property="start_time", type="string", description="Hora de inicio", example="00:00"),
     *                 @OA\Property(property="date_end", type="string", format="date-time", description="Fecha de finalización del evento", example="2025-07-11T05:00:00.000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Reserva creada exitosamente"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Solicitud inválida"
     *     )
     * )
     */
    public function createReservation(
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {

        $data = json_decode($request->getContent(), true);


        $userId = (int) $data['user'];
        $spaceId = (int) $data['space'];


        $user = $entityManager->getRepository(User::class)->find($userId);
        $space = $entityManager->getRepository(Space::class)->find($spaceId);

        $reservation = new Reservation();

        $reservation->setUser($user);
        $reservation->setSpace($space);
        $reservation->setNameEvent($data['name_event']);
        $reservation->setDateStart(new \DateTime($data['date_start']));
        $reservation->setDateEnd(new \DateTime($data['date_end']));

        //Obtenemos los horarios del espacio
        $schedules = $space->getSchedules();
        if (!empty($schedules)) {
            // lo decodificamos para poderlo modificar
            $schedules = json_decode($schedules);

            foreach ($schedules as $key => $value) {
                $date = new \DateTime($value->date);
                $dateNew = new \DateTime($data['date_start']);

                $formattedDate = $date->format('Y-m-d');
                $formattedNewDate = $dateNew->format('Y-m-d');
                //Validamos que si es igual al enviado, entonces que me cambie el available a true ya que es el que me dice si esta disponible
                if ($formattedDate == $formattedNewDate && $value->startTime == $data['start_time']) {
                    $value->available = true;
                }
            }
        }

        //Asigmos el nuevo horaio
        $space->setSchedules(json_encode($schedules));
        //actualizamos el space
        $entityManager->persist($space);


        $entityManager->persist($reservation);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Reserva creada con éxito', 'id' => $reservation->getId()], JsonResponse::HTTP_CREATED);
    }
}
